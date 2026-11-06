<?php

namespace App\Domains\Community\Services;

use App\Domains\Community\Models\Achievement;
use App\Domains\Community\Models\UserAchievement;
use App\Domains\Community\Models\UserAction;
use App\Domains\Auth\Models\User;
use Illuminate\Support\Facades\DB;

class AchievementService
{
    public static array $definitions = [
        ['title_ar' => '🥇 أول خطوة', 'description' => 'أضف أول مكان', 'icon' => '🥇', 'condition_type' => 'add', 'condition_value' => 1, 'xp_reward' => 20],
        ['title_ar' => '🗺️ مكتشف', 'description' => 'أضف 10 أماكن', 'icon' => '🗺️', 'condition_type' => 'add', 'condition_value' => 10, 'xp_reward' => 100],
        ['title_ar' => '🏘️ مؤرخ الأحياء', 'description' => 'أضف 5 أحياء/قرى', 'icon' => '🏘️', 'condition_type' => 'add_neighborhood', 'condition_value' => 5, 'xp_reward' => 200],
        ['title_ar' => '✅ مدقق مبتدئ', 'description' => 'أكد 10 أماكن', 'icon' => '✅', 'condition_type' => 'confirm', 'condition_value' => 10, 'xp_reward' => 50],
        ['title_ar' => '✅ مدقق محترف', 'description' => 'أكد 50 مكاناً', 'icon' => '✅', 'condition_type' => 'confirm', 'condition_value' => 50, 'xp_reward' => 150],
        ['title_ar' => '🔥 النشاط', 'description' => '7 أيام متتالية من التأكيد', 'icon' => '🔥', 'condition_type' => 'streak_7', 'condition_value' => 7, 'xp_reward' => 200],
        ['title_ar' => '💎 موثوق', 'description' => 'وثق 3 نقاط بصورة', 'icon' => '💎', 'condition_type' => 'photos', 'condition_value' => 3, 'xp_reward' => 100],
        ['title_ar' => '⭐ خبير التقييم', 'description' => 'قيّم 20 مكاناً', 'icon' => '⭐', 'condition_type' => 'vote', 'condition_value' => 20, 'xp_reward' => 80],
        ['title_ar' => '🏆 أسطورة', 'description' => 'احصل على 2000 XP', 'icon' => '🏆', 'condition_type' => 'points', 'condition_value' => 2000, 'xp_reward' => 500],
    ];

    public static function seed(): void
    {
        foreach (self::$definitions as $a) {
            Achievement::firstOrCreate(
                ['title_ar' => $a['title_ar']],
                $a
            );
        }
    }

    public static function checkAndAward(string $userId): array
    {
        $newAwards = [];

        $achievements = Achievement::where('active', true)->get();

        foreach ($achievements as $ach) {
            $alreadyHave = UserAchievement::where('user_id', $userId)
                ->where('achievement_id', $ach->id)
                ->exists();

            if ($alreadyHave) continue;

            $earned = match ($ach->condition_type) {
                'add' => UserAction::where('user_id', $userId)->byType('add')->count() >= $ach->condition_value,
                'confirm' => UserAction::where('user_id', $userId)->byType('confirm')->count() >= $ach->condition_value,
                'vote' => UserAction::where('user_id', $userId)->byType('vote')->count() >= $ach->condition_value,
                'streak_7' => self::checkStreak($userId, $ach->condition_value),
                'photos' => UserAction::where('user_id', $userId)->byType('add')
                    ->whereRaw("payload->>'has_image' = '1'")->count() >= $ach->condition_value,
                'points' => UserAction::where('user_id', $userId)->sum('points') >= $ach->condition_value,
                'add_neighborhood' => self::checkNeighborhoodAdds($userId, $ach->condition_value),
                default => false,
            };

            if ($earned) {
                UserAchievement::create([
                    'user_id' => $userId,
                    'achievement_id' => $ach->id,
                    'earned_at' => now(),
                ]);

                if ($ach->xp_reward > 0) {
                    UserAction::create([
                        'user_id' => $userId,
                        'action_type' => 'quest_reward',
                        'payload' => ['achievement_id' => $ach->id, 'achievement_title' => $ach->title_ar],
                        'points' => $ach->xp_reward,
                    ]);
                }

                $newAwards[] = $ach->title_ar;
            }
        }

        return $newAwards;
    }

    public static function getUserAchievements(string $userId): array
    {
        $all = Achievement::where('active', true)->get();
        $userAchs = UserAchievement::where('user_id', $userId)->pluck('achievement_id')->toArray();

        $result = [];
        foreach ($all as $a) {
            $result[] = [
                'id' => $a->id,
                'title' => $a->title_ar,
                'description' => $a->description,
                'icon' => $a->icon,
                'xp_reward' => $a->xp_reward,
                'earned' => in_array($a->id, $userAchs),
                'earned_at' => in_array($a->id, $userAchs)
                    ? UserAchievement::where('user_id', $userId)->where('achievement_id', $a->id)->first()?->earned_at?->diffForHumans()
                    : null,
            ];
        }

        return $result;
    }

    private static function checkStreak(string $userId, int $target): bool
    {
        $dates = UserAction::where('user_id', $userId)
            ->where('action_type', 'confirm')
            ->where('status', 'active')
            ->select(DB::raw("DISTINCT DATE(created_at) as d"))
            ->orderBy('d', 'desc')
            ->get()
            ->pluck('d');

        if ($dates->isEmpty()) return false;

        $streak = 1;
        for ($i = 1; $i < $dates->count(); $i++) {
            $prev = \Carbon\Carbon::parse($dates[$i - 1]);
            $curr = \Carbon\Carbon::parse($dates[$i]);
            if ($prev->diffInDays($curr) === 1) $streak++;
            else break;
        }

        return $streak >= $target;
    }

    private static function checkNeighborhoodAdds(string $userId, int $target): bool
    {
        return UserAction::where('user_id', $userId)->byType('add')->count() >= $target;
    }
}
