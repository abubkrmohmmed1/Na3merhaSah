<?php

namespace App\Domains\Spatial\Services;

use App\Domains\Spatial\Models\GeoFeature;
use App\Domains\Community\Models\UserAction;
use Illuminate\Support\Facades\DB;

class ConfidenceEngine
{
    /**
     * Calculate and update confidence score for a specific feature.
     */
    public static function updateFeatureScore(string $featureId): int
    {
        $feature = GeoFeature::findOrFail($featureId);
        
        $score = 0;
        $level = 'OSM';

        // 1. Check Source (Initial Score)
        $source = $feature->properties['source'] ?? 'OSM';
        if ($source === 'OSM') $score = 40;
        if ($source === 'boundary_challenge') $score = 60;
        if ($source === 'business_claim') $score = 80;

        // 1.1 External Verification (Asynchronous)
        $props = $feature->properties ?? [];
        if (isset($props['external_verified_osm']) && $props['external_verified_osm'] === true) {
            $score += 40; // OSM confidence boost
        } else {
            // Dispatch OSM check in background if not checked yet
            if (!isset($props['external_checked_osm'])) {
                \App\Jobs\VerifyFeatureWithOSMJob::dispatch($featureId);
            }
        }

        // 2. Count Verifications (Confirmations)
        $confirmations = UserAction::where('geo_feature_id', $featureId)
            ->where('action_type', 'confirm')
            ->where('status', 'active')
            ->join('users', 'users.id', '=', 'user_actions.user_id')
            ->select('user_actions.*', 'users.id as uid')
            ->get();

        foreach ($confirmations as $conf) {
            // Weights based on User Trust Score (simplified for now)
            $userTrust = DB::table('user_actions')
                ->where('user_id', $conf->uid)
                ->where('status', 'active')
                ->sum('points');

            $weight = 5;
            if ($userTrust > 1000) $weight = 15;
            elseif ($userTrust > 500) $weight = 10;

            $score += $weight;
        }

        // 3. Negative signals (Flags)
        $flags = UserAction::where('geo_feature_id', $featureId)
            ->where('action_type', 'flag')
            ->where('status', 'active')
            ->count();
        
        $score -= ($flags * 20);

        // Cap Score
        $score = max(0, min(100, $score));

        // Determine Level
        if ($score >= 95) $level = 'Delivery';
        elseif ($score >= 85) $level = 'Field';
        elseif ($score >= 70) $level = 'Owner';
        elseif ($score >= 50) $level = 'Auto';

        // Update Feature
        $feature->update([
            'confidence_score' => $score,
            'verification_level' => $level,
            'status' => ($score >= 80) ? 'verified' : $feature->status,
            'verified_at' => ($score >= 80 && !$feature->verified_at) ? now() : $feature->verified_at,
        ]);

        return $score;
    }

    /**
     * Batch update for all pending/active features.
     */
    public static function runMaintenance()
    {
        $features = GeoFeature::where('status', '!=', 'rejected')
            ->orderBy('updated_at', 'desc')
            ->limit(100) // Process in chunks
            ->get();

        foreach ($features as $f) {
            self::updateFeatureScore($f->id);
        }
    }
}
