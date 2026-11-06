<?php

namespace App\Domains\Spatial\Services;

use App\Domains\Spatial\Models\GeoFeature;
use App\Domains\Spatial\Models\MapLayer;
use App\Domains\Community\Models\UserAction;
use App\Domains\Auth\Models\User;
use Illuminate\Support\Facades\DB;

class TerritoryService
{
    /**
     * حساب المتصدرين لحي محدد (بناءً على كل النشاطات داخل حدود الحي)
     */
    public static function neighborhoodLeaderboard(string $neighborhoodId, int $limit = 20): array
    {
        $neighborhood = GeoFeature::findOrFail($neighborhoodId);
        $weekStart = now()->startOfWeek();

        // جلب كل المعالم التابعة لهذا الحي (المطاعم، المساجد، إلخ) بالإضافة للحي نفسه
        $featureIds = GeoFeature::where('parent_id', $neighborhoodId)
            ->orWhere('id', $neighborhoodId)
            ->pluck('id');

        // حساب النقاط لكل مستخدم داخل هذا النطاق
        $users = UserAction::select('user_id', DB::raw('SUM(points) as total_points'), DB::raw('COUNT(*) as total_actions'))
            ->whereIn('geo_feature_id', $featureIds)
            ->where('created_at', '>=', $weekStart)
            ->where('status', 'active')
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->take($limit)
            ->get()
            ->map(function ($row, $i) {
                $user = User::find($row->user_id);
                return [
                    'rank' => $i + 1,
                    'user_id' => $row->user_id,
                    'user_name' => $user?->name ?? 'مستخدم',
                    'points' => (int) $row->total_points,
                    'actions' => (int) $row->total_actions,
                    'user_avatar' => mb_substr($user?->name ?? 'م', 0, 1),
                ];
            });

        $totalActions = UserAction::whereIn('geo_feature_id', $featureIds)
            ->where('created_at', '>=', $weekStart)
            ->where('status', 'active')
            ->count();

        $totalContributors = UserAction::whereIn('geo_feature_id', $featureIds)
            ->where('created_at', '>=', $weekStart)
            ->where('status', 'active')
            ->distinct('user_id')
            ->count('user_id');

        return [
            'neighborhood_id' => $neighborhoodId,
            'neighborhood_name' => $neighborhood->properties['name'] ?? '',
            'total_contributors' => $totalContributors,
            'total_actions' => $totalActions,
            'ruler' => $users->isNotEmpty() ? $users->first()['user_name'] : null,
            'leaderboard' => $users,
        ];
    }

    /**
     * جلب ملخص للأحياء النشطة هذا الأسبوع (من هم الحكام حالياً)
     */
    public static function territoryHighlights($userLat = null, $userLng = null): array
    {
        $weekStart = now()->startOfWeek();
        $neighborhoodLayer = MapLayer::where('name', 'الأحياء')->first();
        if (!$neighborhoodLayer) return [];

        if ($userLat !== null && $userLng !== null) {
            // Find neighborhood containing user coordinates
            $myNeighborhood = GeoFeature::where('layer_id', $neighborhoodLayer->id)
                ->whereIn('status', ['active', 'verified'])
                ->whereRaw("ST_Contains(geom::geometry, ST_SetSRID(ST_MakePoint(?, ?), 4326))", [$userLng, $userLat])
                ->first();

            // Fallback to nearest if not contained inside one
            if (!$myNeighborhood) {
                $myNeighborhood = GeoFeature::where('layer_id', $neighborhoodLayer->id)
                    ->whereIn('status', ['active', 'verified'])
                    ->orderByRaw("ST_Distance(geom::geography, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography)", [$userLng, $userLat])
                    ->first();
            }

            $neighborhoods = $myNeighborhood ? collect([$myNeighborhood]) : collect();
        } else {
            // نكتفي بالأحياء التي حصلت فيها نشاطات هذا الأسبوع (للسرعة)
            $activeFeatureIds = UserAction::where('created_at', '>=', $weekStart)
                ->where('status', 'active')
                ->distinct('geo_feature_id')
                ->pluck('geo_feature_id');

            // جلب الأحياء التي تتبع لها هذه المعالم
            $activeNeighborhoodIds = GeoFeature::whereIn('id', $activeFeatureIds)
                ->where('layer_id', $neighborhoodLayer->id)
                ->pluck('id')
                ->concat(
                    GeoFeature::whereIn('id', $activeFeatureIds)
                        ->whereNotNull('parent_id')
                        ->pluck('parent_id')
                )
                ->unique();

            $neighborhoods = GeoFeature::whereIn('id', $activeNeighborhoodIds)
                ->where('layer_id', $neighborhoodLayer->id)
                ->whereIn('status', ['active', 'verified'])
                ->get();
        }

        $highlights = [];
        foreach ($neighborhoods as $n) {
            $data = self::neighborhoodLeaderboard($n->id, 1);
            $coords = DB::selectOne("SELECT ST_X(ST_Centroid(geom)) as lng, ST_Y(ST_Centroid(geom)) as lat FROM geo_features WHERE id = ?", [$n->id]);
            $props = $n->properties ?? [];
            if (is_string($props)) { $props = json_decode($props, true) ?? []; }
            $name = $props['name'] ?? 'حي غير مسمى';
            
            $highlights[] = [
                'neighborhood_id' => $n->id,
                'name' => $name,
                'ruler' => $data['ruler'],
                'total_actions' => $data['total_actions'],
                'total_contributors' => $data['total_contributors'],
                'lat' => $coords?->lat ?? 15.5871,
                'lng' => $coords?->lng ?? 32.5232,
            ];
        }

        // Fallback: If no active neighborhoods this week, and we didn't specify user coordinates, return all verified/active neighborhoods to make the UI populated
        if (empty($highlights) && $userLat === null) {
            $fallbackNeighborhoods = GeoFeature::where('layer_id', $neighborhoodLayer->id)
                ->whereIn('status', ['active', 'verified'])
                ->limit(15)
                ->get();

            foreach ($fallbackNeighborhoods as $n) {
                $coords = DB::selectOne("SELECT ST_X(ST_Centroid(geom)) as lng, ST_Y(ST_Centroid(geom)) as lat FROM geo_features WHERE id = ?", [$n->id]);
                $props = $n->properties ?? [];
                if (is_string($props)) { $props = json_decode($props, true) ?? []; }
                $name = $props['name'] ?? 'حي غير مسمى';

                $highlights[] = [
                    'neighborhood_id' => $n->id,
                    'name' => $name,
                    'ruler' => null,
                    'total_actions' => 0,
                    'total_contributors' => 0,
                    'lat' => $coords?->lat ?? 15.5871,
                    'lng' => $coords?->lng ?? 32.5232,
                ];
            }
        }

        // ترتيب الأحياء حسب الأكثر نشاطاً
        usort($highlights, fn($a, $b) => $b['total_actions'] - $a['total_actions']);

        return $highlights;
    }
}
