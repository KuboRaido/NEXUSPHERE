<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Prc;
use App\Models\User;
use App\Models\Images_and_videos;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class PostService
{
    public function createPost(
        User $user,
        string $sentence,
        array $images = [],
        array $videos = [],
        ?int $circleId = null
    ): Prc {
        $post = Prc::create([
            'user_id' => $user->user_id,
            'sentence' => $sentence,
            'type' => 0,
            'circle_id' => $circleId,
        ]);

        $this->attachMediaToPost($post, $images, $videos, $circleId);

        return $post->load('user', 'images');
    }

    private function attachMediaToPost(
        Prc $post,
        array $images,
        array $videos,
        ?int $circleId = null
    ): void {
        $imagesToStore = is_array($images) ? $images : [];
        $imagesToStore = array_filter($imagesToStore, fn($img) => $img instanceof UploadedFile);
        $imagesToStore = array_slice($imagesToStore, 0, 10);

        foreach ($imagesToStore as $image) {
            $path = $image->store('', 'post');
            Images_and_videos::create([
                'prc_id' => $post->prc_id,
                'image' => $path,
                'circle_id' => $circleId,
            ]);
        }

        $videosToStore = is_array($videos) ? $videos : [];
        $videosToStore = array_filter($videosToStore, fn($vid) => $vid instanceof UploadedFile);

        foreach ($videosToStore as $video) {
            $path = $video->store('', 'post');
            Images_and_videos::create([
                'prc_id' => $post->prc_id,
                'video' => $path,
                'circle_id' => $circleId,
            ]);
        }
    }
}
