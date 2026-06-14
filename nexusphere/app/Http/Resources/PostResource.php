<?php declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'prc_id' => $this->prc_id,
            'user_id' => $this->user_id,
            'sentence' => $this->sentence,
            'type' => $this->type,
            'circle_id' => $this->circle_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => $this->whenLoaded('user', fn() => [
                'user_id' => $this->user?->user_id,
                'name' => $this->user?->name,
                'icon' => $this->user?->icon,
            ]),
            'images' => $this->whenLoaded('images', fn() => $this->images->map(fn($img) => [
                'image_and_video_id' => $img->image_and_video_id,
                'url' => $img->url,
                'type' => $img->type,
            ])),
            'comments_count' => $this->whenCounted('comments'),
            'likes_count' => $this->whenCounted('nices'),
        ];
    }
}
