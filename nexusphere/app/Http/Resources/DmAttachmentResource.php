<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * DMの添付ファイル（Images_and_videos）1件分のJSON
 *
 * モデルの url アクセサは投稿(prc)と共用でディスクが異なるため、
 * DM用の公開パス storage/dms/ はここで組み立てる
 */
class DmAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $path = $this->image ?: $this->video;

        return [
            'type' => $this->type,
            'url'  => $path ? asset('storage/dms/' . $path) : null,
        ];
    }
}
