<?php

namespace App\Domains\Spatial\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuestBridgeService
{
    /**
     * Create quests for items in a B2B order.
     */
    public static function bridgeOrderToQuests(string $orderId)
    {
        $items = DB::table('verification_items')
            ->where('order_id', $orderId)
            ->select('*', DB::raw('ST_X(target_geom::geometry) as lng'), DB::raw('ST_Y(target_geom::geometry) as lat'))
            ->get();

        foreach ($items as $item) {
            // Create a Quest for each verification item
            DB::table('quests')->insert([
                'id' => Str::uuid(),
                'title' => 'توثيق: ' . $item->target_name,
                'description' => 'الشركة تحتاج تأكيد دقيق لموقع: ' . $item->target_name,
                'reward_points' => 150, // Match the order price per item scale
                'is_active' => true,
                'target_layer' => 'المنشآت',
                'target_action' => 'confirm',
                'payload' => json_encode([
                    'verification_item_id' => $item->id,
                    'order_id' => $orderId
                ]),
                'starts_at' => now(),
                'ends_at' => now()->addDays(7),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
