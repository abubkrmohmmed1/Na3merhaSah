<?php

namespace App\Domains\Community\Services;

use App\Domains\Community\Models\Quest;
use App\Domains\Community\Models\UserQuest;
use App\Domains\Community\Models\UserAction;
use App\Domains\Auth\Models\User;
use Illuminate\Support\Facades\DB;

class QuestService
{
    // Default quests
    public static array $defaultQuests = [
        ['title_ar' => 'أكد 3 أماكن', 'description' => 'أكد 3 أماكن مختلفة اليوم', 'type' => 'daily', 'goal_type' => 'confirm', 'goal_count' => 3, 'xp_reward' => 30, 'icon' => '✅'],
        ['title_ar' => 'أضف مكاناً واحداً', 'description' => 'سجل مكاناً واحداً جديداً', 'type' => 'daily', 'goal_type' => 'add', 'goal_count' => 1, 'xp_reward' => 50, 'icon' => '➕'],
        ['title_ar' => 'سجل الدخول اليوم', 'description' => 'افتح التطبيق وسجل الدخول', 'type' => 'daily', 'goal_type' => 'login', 'goal_count' => 1, 'xp_reward' => 10, 'icon' => '🔑'],
        ['title_ar' => 'أكد 10 أماكن', 'description' => 'أكد 10 أماكن هذا الأسبوع', 'type' => 'weekly', 'goal_type' => 'confirm', 'goal_count' => 10, 'xp_reward' => 150, 'icon' => '🏆'],
        ['title_ar' => 'أضف 5 أماكن', 'description' => 'سجل 5 أماكن جديدة هذا الأسبوع', 'type' => 'weekly', 'goal_type' => 'add', 'goal_count' => 5, 'xp_reward' => 200, 'icon' => '🌟'],
        ['title_ar' => 'قيّم 5 أماكن', 'description' => 'قيّم 5 أماكن مختلفة هذا الأسبوع', 'type' => 'weekly', 'goal_type' => 'vote', 'goal_count' => 5, 'xp_reward' => 80, 'icon' => '⭐'],
    ];

    public static function seedQuests(): void
    {
        foreach (self::$defaultQuests as $q) {
            Quest::firstOrCreate(
                ['title_ar' => $q['title_ar'], 'type' => $q['type']],
                $q
            );
        }
    }

    public static function getUserQuests(string $userId): array
    {
        $today = now()->startOfDay();
        $weekStart = now()->startOfWeek();

        $quests = Quest::where('active', true)->get();
        $result = [];

        foreach ($quests as $quest) {
            $userQuest = UserQuest::firstOrCreate(
                ['user_id' => $userId, 'quest_id' => $quest->id],
                ['progress' => 0]
            );

            // Reset progress if new period
            if ($quest->type === 'daily') {
                if (!$userQuest->updated_at || $userQuest->updated_at->startOfDay()->lt($today)) {
                    $userQuest->update(['progress' => 0, 'completed_at' => null, 'claimed_at' => null]);
                }
                $periodStart = $today;
            } else {
                if (!$userQuest->updated_at || $userQuest->updated_at->startOfWeek()->lt($weekStart)) {
                    $userQuest->update(['progress' => 0, 'completed_at' => null, 'claimed_at' => null]);
                }
                $periodStart = $weekStart;
            }

            // Calculate actual progress from actions
            $progress = UserAction::where('user_id', $userId)
                ->where('action_type', $quest->goal_type)
                ->where('created_at', '>=', $periodStart)
                ->count();

            $userQuest->update(['progress' => min($progress, $quest->goal_count)]);

            $result[] = [
                'id' => $quest->id,
                'title' => $quest->title_ar,
                'description' => $quest->description,
                'type' => $quest->type,
                'goal_type' => $quest->goal_type,
                'goal_count' => $quest->goal_count,
                'xp_reward' => $quest->xp_reward,
                'icon' => $quest->icon,
                'progress' => min($progress, $quest->goal_count),
                'completed' => $progress >= $quest->goal_count,
                'claimed' => $userQuest->claimed_at !== null,
            ];
        }

        return $result;
    }

    public static function claimQuest(string $userId, string $questId): array
    {
        $quest = Quest::findOrFail($questId);
        $userQuest = UserQuest::where('user_id', $userId)
            ->where('quest_id', $questId)
            ->firstOrFail();

        if ($userQuest->claimed_at) {
            return ['success' => false, 'message' => 'لقد استلمت المكافأة مسبقاً'];
        }

        $periodStart = $quest->type === 'daily' ? now()->startOfDay() : now()->startOfWeek();
        $progress = UserAction::where('user_id', $userId)
            ->where('action_type', $quest->goal_type)
            ->where('created_at', '>=', $periodStart)
            ->count();

        if ($progress < $quest->goal_count) {
            return ['success' => false, 'message' => 'لم تكمل المهمة بعد'];
        }

        $userQuest->update([
            'progress' => $quest->goal_count,
            'completed_at' => now(),
            'claimed_at' => now(),
        ]);

        // Award XP via UserAction
        UserAction::create([
            'user_id' => $userId,
            'action_type' => 'quest_reward',
            'points' => $quest->xp_reward,
            'payload' => ['quest_id' => $questId, 'quest_title' => $quest->title_ar],
        ]);

        return [
            'success' => true,
            'message' => "🎉 استلمت {$quest->xp_reward} XP",
            'xp_earned' => $quest->xp_reward,
        ];
    }
}
