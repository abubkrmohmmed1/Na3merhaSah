<?php

namespace App\Domains\Spatial\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuestBridgeService
{
    /**
     * Create quests for items in a B2B order.
     */
    public function bridgeOrderToQuests(string $orderId): void
    {
        $items = DB::table('verification_items')
            ->where('order_id', $orderId)
            ->get();

        foreach ($items as $item) {
            DB::table('quests')->insert([
                'id' => (string) Str::uuid(),
                'title_ar' => 'توثيق: ' . ($item->target_name ?? 'نقطة ميدانية'),
                'description' => 'الشركة تحتاج تأكيد دقيق لموقع: ' . ($item->target_name ?? 'نقطة ميدانية'),
                'type' => 'daily',
                'goal_type' => 'confirm',
                'goal_count' => 1,
                'xp_reward' => 150,
                'icon' => 'location',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
