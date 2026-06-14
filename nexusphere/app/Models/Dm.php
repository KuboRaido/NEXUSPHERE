<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dm extends Model
{
    use SoftDeletes;

    protected $table = ‘dms’;
    protected $primaryKey = ‘dm_id’;
    public $incrementing = true;
    protected $keyType = ‘int’;

    protected $fillable = [
        ‘circle_id’,
        ‘user_id’,
        ‘group_id’,
        ‘sender_id’,
        ‘receiver_id’,
        ‘message_text’,
        ‘conversation_id’,
        ‘attachments’,
        ‘parent_id’,
        ‘reply_to_dm_id’,
    ];

    protected $casts = [‘attachments’ => ‘array’];

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(self::class, ‘reply_to_dm_id’, ‘dm_id’);
    }

    public function repliedBy(): HasMany
    {
        return $this->hasMany(self::class, ‘reply_to_dm_id’, ‘dm_id’);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, ‘parent_id’, ‘dm_id’);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, ‘parent_id’, ‘dm_id’);
    }

    public function circle(): BelongsTo
    {
        return $this->belongsTo(Circle::class, ‘circle_id’, ‘circle_id’);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, ‘group_id’, ‘group_id’);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, ‘sender_id’, ‘user_id’);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, ‘receiver_id’, ‘user_id’);
    }

    public function Images_and_videos(): HasMany
    {
        return $this->hasMany(Images_and_videos::class, ‘dm_id’, ‘dm_id’);
    }

    protected static function booted(): void
    {
        static::saving(function (Dm $dm): void {
            if (!empty($dm->circle_id)) {
                return;
            }

            if (!empty($dm->group_id)) {
                return;
            }

            if (isset($dm->sender_id, $dm->receiver_id)) {
                $a = (int)$dm->sender_id;
                $b = (int)$dm->receiver_id;
                $low = min($a, $b);
                $high = max($a, $b);
                $dm->dm_key = “{$low}-{$high}”;
                return;
            }

            throw new \InvalidArgumentException(‘sender_idとreceiver_idは必須です。’);
        });
    }
}
