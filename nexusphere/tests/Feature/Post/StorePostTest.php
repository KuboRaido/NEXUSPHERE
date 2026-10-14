<?php

use App\Models\User;

it('投稿すると prcs に保存される', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/post', [
        'sentence' => 'テスト投稿',
    ]);

    $this->assertDatabaseHas('prcs', [
        'user_id' => $user->user_id,
        'sentence' => 'テスト投稿',
        'type' => 0,
    ]);
});