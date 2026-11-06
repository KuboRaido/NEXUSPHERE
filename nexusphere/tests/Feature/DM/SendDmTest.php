<?php

use App\Models\User;

it('DMを送信すると201が返り dms に保存される', function () {
    $sender = User::factory()->create();
    $receiver = User::factory()->create();

    $response = $this->actingAs($sender)->postJson('/api/v1/dm', [
        'to' => $receiver->user_id,
        'text' => 'こんにちは',
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('dms', [
        'sender_id' => $sender->user_id,
        'receiver_id' => $receiver->user_id,
    ]);
});