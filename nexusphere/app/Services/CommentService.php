<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Prc;
use App\Models\User;

class CommentService
{
    public function create(Prc $parent, User $user, string $sentence): Prc
    {
        $comment = $parent->comments()->create([
            'sentence' => $sentence,
            'user_id' => $user->user_id,
            'type' => 1,
        ]);

        return $comment->load('user');
    }
}
