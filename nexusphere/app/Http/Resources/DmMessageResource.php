<?php

namespace App\Http\Resources;

use App\Support\TextHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 1対1 DMのメッセージ1件分のJSON
 *
 * 会話ログ取得(dmback)と送信直後のレスポンス(dmsendback)で共用する。
 * is_read は withReadStatus() を呼んだときだけ出力する
 */
class DmMessageResource extends JsonResource
{
    public static $wrap = null;

    private bool $withReadStatus = false;
    private ?int $meId = null;
    private ?string $partnerReadAt = null;

    # 相手の既読時刻をもとに is_read を付ける
    public function withReadStatus(int $meId, ?string $partnerReadAt): static
    {
        $this->withReadStatus = true;
        $this->meId = $meId;
        $this->partnerReadAt = $partnerReadAt;

        return $this;
    }

    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->dm_id,
            'from_id'     => (int) $this->sender_id,
            'to_id'       => (int) $this->receiver_id,
            'text'        => TextHelper::linkify($this->message_text ?? ''),
            'dm_key'      => $this->dm_key,
            'created_at'  => $this->created_at?->toISOString(),
            'is_read'     => $this->when($this->withReadStatus, fn () => $this->isReadByPartner()),
            'attachments' => DmAttachmentResource::collection($this->Images_and_videos),
        ];
    }

    # 自分が送ったメッセージで、相手の既読時刻以前に作られていれば既読
    private function isReadByPartner(): bool
    {
        $isMine = (int) $this->sender_id === $this->meId;

        return $isMine && $this->partnerReadAt ? $this->created_at <= $this->partnerReadAt : false;
    }
}
