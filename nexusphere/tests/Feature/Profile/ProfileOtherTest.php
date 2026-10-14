<?php

use App\Models\Prc;
use App\Models\User;

it('他人のプロフィールにコメントが投稿として表示されない', function () {
    // 準備
    $viewer = User::factory()->create();
    $author = User::factory()->create();

    $post = Prc::create([
        'user_id'  => $author->user_id,
        'sentence' => '元の投稿です',
        'type'     => 0,
    ]);

    Prc::create([
        'user_id'   => $author->user_id,
        'sentence'  => 'これはコメントです',
        'type'      => 1,
        'parent_id' => $post->prc_id,
    ]);

    // 実行
    $response = $this->actingAs($viewer)->get("/profile/{$author->user_id}");

    // 確認
    $response->assertOk();
    $response->assertSee('元の投稿です');
    $response->assertDontSee('これはコメントです');
});