<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Prc;
use App\Models\Nice;
use App\Models\Circle;
use App\Models\Circle_user;
use App\Models\Images_and_videos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PrcControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Circle $circle;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('post');

        $this->user = User::factory()->create();
        $this->circle = Circle::factory()->create();
        Circle_user::create([
            'circle_id' => $this->circle->circle_id,
            'user_id' => $this->user->user_id,
            'role' => 'member',
        ]);
    }

    public function test_store_creates_post_with_text(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/posts', [
            'sentence' => 'This is my first post',
        ]);

        $this->assertDatabaseHas('prcs', [
            'user_id' => $this->user->user_id,
            'sentence' => 'This is my first post',
            'type' => 0,
            'circle_id' => null,
        ]);
    }

    public function test_store_with_images_attaches_to_images_and_videos(): void
    {
        $this->actingAs($this->user);

        $files = [
            UploadedFile::fake()->image('photo1.jpg'),
            UploadedFile::fake()->image('photo2.png'),
        ];

        $response = $this->post('/posts', [
            'sentence' => 'Post with images',
            'images' => $files,
        ]);

        $post = Prc::where('sentence', 'Post with images')->first();
        $this->assertNotNull($post);

        $attachments = Images_and_videos::where('prc_id', $post->prc_id)->get();
        $this->assertCount(2, $attachments);
        $this->assertTrue($attachments->every(fn($img) => !is_null($img->image)));
    }

    public function test_store_requires_sentence(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/posts', [
            'sentence' => '',
        ]);

        $this->assertDatabaseMissing('prcs', [
            'user_id' => $this->user->user_id,
        ]);
    }

    public function test_store_sentence_max_length(): void
    {
        $this->actingAs($this->user);

        $longText = str_repeat('a', 1001);

        $response = $this->post('/posts', [
            'sentence' => $longText,
        ]);

        $this->assertDatabaseMissing('prcs', [
            'user_id' => $this->user->user_id,
            'sentence' => $longText,
        ]);
    }

    public function test_circle_store_creates_post_with_circle(): void
    {
        $this->actingAs($this->user);

        $response = $this->post("/circles/{$this->circle->circle_id}/posts", [
            'sentence' => 'Circle post',
            'circle_id' => $this->circle->circle_id,
        ]);

        $this->assertDatabaseHas('prcs', [
            'user_id' => $this->user->user_id,
            'sentence' => 'Circle post',
            'circle_id' => $this->circle->circle_id,
            'type' => 0,
        ]);
    }

    public function test_circle_store_requires_membership(): void
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $response = $this->post("/circles/{$this->circle->circle_id}/posts", [
            'sentence' => 'Unauthorized post',
            'circle_id' => $this->circle->circle_id,
        ]);

        $response->assertStatus(403);
    }

    public function test_like_toggle_creates_nice(): void
    {
        $this->actingAs($this->user);

        $post = Prc::factory()->create([
            'user_id' => User::factory()->create()->user_id,
            'type' => 0,
        ]);

        $response = $this->post("/posts/{$post->prc_id}/like");

        $this->assertDatabaseHas('nices', [
            'prc_id' => $post->prc_id,
            'user_id' => $this->user->user_id,
        ]);
    }

    public function test_like_toggle_removes_nice(): void
    {
        $this->actingAs($this->user);

        $post = Prc::factory()->create([
            'user_id' => User::factory()->create()->user_id,
            'type' => 0,
        ]);

        Nice::create([
            'prc_id' => $post->prc_id,
            'user_id' => $this->user->user_id,
        ]);

        $response = $this->post("/posts/{$post->prc_id}/like");

        $this->assertDatabaseMissing('nices', [
            'prc_id' => $post->prc_id,
            'user_id' => $this->user->user_id,
        ]);
    }

    public function test_like_json_response_returns_liked_status(): void
    {
        $this->actingAs($this->user);

        $post = Prc::factory()->create([
            'user_id' => User::factory()->create()->user_id,
            'type' => 0,
        ]);

        $response = $this->postJson("/posts/{$post->prc_id}/like");

        $response->assertJsonStructure(['liked', 'like_count']);
        $response->assertJson(['liked' => true]);
    }

    public function test_like_json_response_returns_updated_count(): void
    {
        $this->actingAs($this->user);

        $post = Prc::factory()->create([
            'user_id' => User::factory()->create()->user_id,
            'type' => 0,
        ]);

        $otherUser = User::factory()->create();
        Nice::create([
            'prc_id' => $post->prc_id,
            'user_id' => $otherUser->user_id,
        ]);

        $response = $this->postJson("/posts/{$post->prc_id}/like");

        $response->assertJson(['like_count' => 2]);
    }

    public function test_comment_creates_child_prc_record(): void
    {
        $this->actingAs($this->user);

        $parent = Prc::factory()->create([
            'user_id' => User::factory()->create()->user_id,
            'type' => 0,
        ]);

        $response = $this->post("/posts/{$parent->prc_id}/comment", [
            'comment' => 'Great post!',
        ]);

        $this->assertDatabaseHas('prcs', [
            'parent_id' => $parent->prc_id,
            'user_id' => $this->user->user_id,
            'sentence' => 'Great post!',
            'type' => 1,
        ]);
    }

    public function test_comment_requires_text(): void
    {
        $this->actingAs($this->user);

        $parent = Prc::factory()->create([
            'user_id' => User::factory()->create()->user_id,
            'type' => 0,
        ]);

        $response = $this->post("/posts/{$parent->prc_id}/comment", [
            'comment' => '',
        ]);

        $this->assertDatabaseMissing('prcs', [
            'parent_id' => $parent->prc_id,
            'user_id' => $this->user->user_id,
        ]);
    }

    public function test_delete_removes_post(): void
    {
        $this->actingAs($this->user);

        $post = Prc::factory()->create([
            'user_id' => $this->user->user_id,
            'type' => 0,
        ]);

        $response = $this->post("/posts/{$post->prc_id}/delete");

        $this->assertDatabaseMissing('prcs', [
            'prc_id' => $post->prc_id,
        ]);
    }

    public function test_delete_prevents_deleting_others_post(): void
    {
        $otherUser = User::factory()->create();
        $this->actingAs($this->user);

        $post = Prc::factory()->create([
            'user_id' => $otherUser->user_id,
            'type' => 0,
        ]);

        $response = $this->post("/posts/{$post->prc_id}/delete");

        $this->assertDatabaseHas('prcs', [
            'prc_id' => $post->prc_id,
        ]);
    }
}
