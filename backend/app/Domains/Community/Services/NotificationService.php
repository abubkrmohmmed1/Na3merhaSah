<?php

namespace App\Domains\Community\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Send a notification to a specific user.
     */
    public static function send($userId, string $title, string $message, string $type = 'info', array $payload = [])
    {
        return DB::table('app_notifications')->insert([
            'id' => Str::uuid(),
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'payload' => json_encode($payload),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Notify multiple users at once.
     */
    public static function notifyMany(array $userIds, string $title, string $message, string $type = 'info', array $payload = [])
    {
        $data = array_map(fn($id) => [
            'id' => Str::uuid(),
            'user_id' => $id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'payload' => json_encode($payload),
            'created_at' => now(),
            'updated_at' => now(),
        ], $userIds);

        return DB::table('app_notifications')->insert($data);
    }

    /**
     * Get unread notifications for a user.
     */
    public static function getUnread($userId)
    {
        return DB::table('app_notifications')
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public static function markAllAsRead($userId)
    {
        return DB::table('app_notifications')
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
