<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Prc;
use App\Models\Nice;
use App\Models\User;

class LikeService
{
    public function toggle(Prc $post, User $user): array
    {
        $existing = Nice::where('prc_id', $post->prc_id)
            ->where('user_id', $user->user_id)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            Nice::firstOrCreate([
                'prc_id' => $post->prc_id,
                'user_id' => $user->user_id,
            ]);
            $liked = true;
        }

        $likeCount = Nice::where('prc_id', $post->prc_id)->count();

        return [
            'liked' => $liked,
            'like_count' => $likeCount,
        ];
    }

    public function getCount(Prc $post): int
    {
        return Nice::where('prc_id', $post->prc_id)->count();
    }

    public function getLikedUsers(Prc $post): array
    {
        return Nice::where('prc_id', $post->prc_id)
            ->with('user:user_id,name,icon')
            ->get()
            ->map(function ($nice) {
                return [
                    'user_id' => $nice->user?->user_id,
                    'name' => $nice->user?->name,
                    'icon' => $nice->user?->icon,
                ];
            })
            ->filter(fn($u) => !is_null($u['user_id']))
            ->values()
            ->toArray();
    }
}
