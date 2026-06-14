<?php declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class UnreadService
{
    public function markAsRead(int $userId, int $conversationId, string $type): int
    {
        match ($type) {
            'direct' => $this->markDirectAsRead($userId, $conversationId),
            'circle' => $this->markCircleAsRead($userId, $conversationId),
            'group' => $this->markGroupAsRead($userId, $conversationId),
            default => throw new \InvalidArgumentException("Invalid type: {$type}"),
        };

        return $this->getUnreadCount($userId, $conversationId, $type);
    }

    private function markDirectAsRead(int $userId, int $partnerId): void
    {
        DB::table('dm_reads')->upsert(
            [[
                'user_id' => $userId,
                'partner_id' => $partnerId,
                'last_read_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]],
            ['user_id', 'partner_id'],
            ['last_read_at', 'updated_at']
        );
    }

    private function markCircleAsRead(int $userId, int $circleId): void
    {
        DB::table('dm_reads')->upsert(
            [[
                'user_id' => $userId,
                'circle_id' => $circleId,
                'last_read_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]],
            ['user_id', 'circle_id'],
            ['last_read_at', 'updated_at']
        );
    }

    private function markGroupAsRead(int $userId, int $groupId): void
    {
        DB::table('dm_reads')->upsert(
            [[
                'user_id' => $userId,
                'group_id' => $groupId,
                'last_read_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]],
            ['user_id', 'group_id'],
            ['last_read_at', 'updated_at']
        );
    }

    private function getUnreadCount(int $userId, int $conversationId, string $type): int
    {
        return match ($type) {
            'direct' => DB::table('dms')
                ->where('receiver_id', $userId)
                ->where('sender_id', $conversationId)
                ->where('created_at', '>', DB::table('dm_reads')
                    ->where('user_id', $userId)
                    ->where('partner_id', $conversationId)
                    ->value('last_read_at') ?? '1970-01-01')
                ->count(),

            'circle' => DB::table('dms')
                ->where('circle_id', $conversationId)
                ->where('created_at', '>', DB::table('dm_reads')
                    ->where('user_id', $userId)
                    ->where('circle_id', $conversationId)
                    ->value('last_read_at') ?? '1970-01-01')
                ->count(),

            'group' => DB::table('dms')
                ->where('group_id', $conversationId)
                ->where('created_at', '>', DB::table('dm_reads')
                    ->where('user_id', $userId)
                    ->where('group_id', $conversationId)
                    ->value('last_read_at') ?? '1970-01-01')
                ->count(),

            default => 0,
        };
    }
}
