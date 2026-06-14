<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Images_and_videos extends Model
{
    protected $table = 'images_and_videos';
    protected $primaryKey = 'image_and_video_id';

    protected $fillable = [
        'prc_id',
        'video',
        'image',
        'dm_id',
        'circle_id',
    ];

    protected $appends = ['url', 'type'];

    public function getUrlAttribute(): ?string
    {
        $path = $this->image ?? $this->video;
        return $path ? Storage::url($path) : null;
    }

    public function getTypeAttribute(): string
    {
        if ($this->image) {
            return 'image';
        }
        if ($this->video) {
            return 'video';
        }
        return 'file';
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Dm::class, 'dm_id', 'dm_id');
    }
}
