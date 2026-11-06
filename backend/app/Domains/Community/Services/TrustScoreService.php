<?php

namespace App\Domains\Community\Services;

use App\Domains\Community\Models\UserAction;
use App\Domains\Auth\Models\User;

class TrustScoreService
{
    /**
     * Calculate a user's trust score (0-100).
     *
     * Factors:
     * - Confirmation accuracy (how many confirmed places stay verified)
     * - Total actions volume
     * - Account age
     * - Current streak
     * - Has verification photos
     */
    public static function calculate(string $userId): array
    {
        $user = User::find($userId);
        if (!$user) {
            return ['score' => 0, 'level' => 'غير معروف', 'factors' => []];
        }

        $totalConfirms = UserAction::where('user_id', $userId)
            ->where('action_type', 'confirm')
            ->where('status', 'active')
            ->count();

        $totalAdds = UserAction::where('user_id', $userId)
            ->where('action_type', 'add')
            ->where('status', 'active')
            ->count();

        $totalFlags = UserAction::where('user_id', $userId)
            ->where('action_type', 'flag')
            ->where('status', 'active')
            ->count();

        $totalVotes = UserAction::where('user_id', $userId)
            ->where('action_type', 'vote')
            ->where('status', 'active')
            ->count();

        $totalActions = $totalConfirms + $totalAdds + $totalFlags + $totalVotes;

        // Factor 1: Actions volume (max 30 points)
        $volumeScore = min(30, $totalActions * 2);

        // Factor 2: Account age (max 20 points)
        $daysSinceRegistration = max(1, $user->created_at->diffInDays(now()));
        $ageScore = min(20, $daysSinceRegistration * 0.5);

        // Factor 3: Streak (max 20 points) — count consecutive active days
        $confirmDates = UserAction::where('user_id', $userId)
            ->where('action_type', 'confirm')
            ->where('status', 'active')
            ->select(\Illuminate\Support\Facades\DB::raw("DISTINCT DATE(created_at) as d"))
            ->orderBy('d', 'desc')
            ->get()
            ->pluck('d');

        $streak = 0;
        foreach ($confirmDates as $i => $date) {
            $d = \Carbon\Carbon::parse($date);
            if ($i === 0) { $streak = $d->isToday() || $d->isYesterday() ? 1 : 0; continue; }
            $prev = \Carbon\Carbon::parse($confirmDates[$i - 1]);
            if ($prev->diffInDays($d) === 1) $streak++;
            else break;
        }
        $streakScore = min(20, $streak * 3);

        // Factor 4: Completed quests (max 15 points)
        $questCount = UserAction::where('user_id', $userId)
            ->where('action_type', 'quest_reward')
            ->where('status', 'active')
            ->count();
        $questScore = min(15, $questCount * 2);

        // Factor 5: Verification photos (max 15 points)
        $photoCount = UserAction::where('user_id', $userId)
            ->where('action_type', 'add')
            ->where('status', 'active')
            ->whereRaw("payload->>'has_image' = '1'")
            ->count();
        $photoScore = min(15, $photoCount * 3);

        $totalScore = (int) round($volumeScore + $ageScore + $streakScore + $questScore + $photoScore);

        // Trust level
        $level = match (true) {
            $totalScore >= 80 => '🟢 موثوق جداً',
            $totalScore >= 60 => '🔵 موثوق',
            $totalScore >= 40 => '🟡 متوسط',
            $totalScore >= 20 => '🟠 جديد',
            default => '🔴 غير معروف',
        };

        // Vote weight: how many "virtual confirms" this user equals
        $voteWeight = match (true) {
            $totalScore >= 90 => 5,   // Super trusted
            $totalScore >= 75 => 3,   // Very trusted
            $totalScore >= 50 => 2,   // Trusted
            default => 1,             // Standard
        };

        return [
            'score' => $totalScore,
            'level' => $level,
            'vote_weight' => $voteWeight,
            'streak_days' => $streak,
            'factors' => [
                'volume' => ['value' => $volumeScore, 'label' => 'كثافة النشاطات', 'total' => $totalActions],
                'age' => ['value' => $ageScore, 'label' => 'مدة العضوية', 'days' => (int) $daysSinceRegistration],
                'streak' => ['value' => $streakScore, 'label' => 'النشاط المتتالي', 'days' => $streak],
                'quests' => ['value' => $questScore, 'label' => 'المهام المنجزة', 'count' => $questCount],
                'photos' => ['value' => $photoScore, 'label' => 'الصور المؤكدة', 'count' => $photoCount],
            ],
        ];
    }
}
