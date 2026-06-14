<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Dm;
use App\Models\Images_and_videos;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class DirectMessageService
{
    public function send(
        User $sender,
        string $text = '',
        array $files = [],
        ?int $partnerId = null,
        ?int $circleId = null,
        ?int $groupId = null
    ): Dm {
        $dm = Dm::create([
            'sender_id' => $sender->user_id,
            'receiver_id' => $partnerId,
            'message_text' => !empty($text) ? $text : null,
            'user_id' => $sender->user_id,
            'circle_id' => $circleId,
            'group_id' => $groupId,
        ]);

        $this->attachFiles($dm, $files);

        return $dm->load('Images_and_videos');
    }

    private function attachFiles(Dm $dm, array $files): void
    {
        $filesToStore = array_filter($files, fn($f) => $f instanceof UploadedFile);

        foreach ($filesToStore as $file) {
            $path = $file->store('', 'dm');
            $mime = $file->getMimeType();
            $isImage = str_starts_with($mime ?? '', 'image/');
            $isVideo = str_starts_with($mime ?? '', 'video/');

            Images_and_videos::create([
                'image' => $isImage ? $path : null,
                'video' => $isVideo ? $path : null,
                'dm_id' => $dm->dm_id,
            ]);
        }
    }
}
