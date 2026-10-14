<?php

use App\Models\User;
use App\Models\Circle;

it('正しいメールとパスワードでログインでき /home へ移動する', function () {
    // 準備
    $user = User::factory()->create();
    Circle::forceCreate([
        'circle_id' => 7,
        'circle_name' => '公式サークル',
        'sentence' => '公式サークル',
        'owner_id' => $user->user_id,
    ]);

    // 実行
    $response = $this->post('/', [
        'mail' => $user->mail,
        'password' => 'password',
    ]);

    // 確認
    $response->assertRedirect('/home');
    $this->assertAuthenticatedAs($user);
});