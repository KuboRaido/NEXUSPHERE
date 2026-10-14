<?php

namespace App\Http\Resources;

use App\Support\TextHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * サークル・グループなど複数人ルームのメッセージ1件分のJSON
 *
 * 送信者が複数いるため、メッセージごとに送信者アイコンを持つ
 */
class RoomMessageResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->dm_id,
            'from_id'     => $this->sender_id,
            'text'        => TextHelper::linkify($this->message_text ?? ''),
            'icon'        => $this->sender?->avatar_url ?? asset('images/default-avatar.png'),
            'created_at'  => $this->created_at?->toISOString(),
            'is_read'     => $this->is_read,
            'attachments' => DmAttachmentResource::collection($this->Images_and_videos),
        ];
    }
}
