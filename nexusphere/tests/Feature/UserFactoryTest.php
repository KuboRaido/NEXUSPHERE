<?php

use App\Models\User;

it('ファクトリでユーザーを作成できる', function () {
    $user = User::factory()->create();

    expect($user->user_id)->not->toBeNull();
});