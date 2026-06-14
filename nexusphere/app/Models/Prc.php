<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prc extends Model
{
    use HasFactory;

    protected $table = 'prcs';
    protected $primaryKey = 'prc_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'sentence',
        'type',
        'parent_id',
        'circle_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function circle(): BelongsTo
    {
        return $this->belongsTo(Circle::class, 'circle_id', 'circle_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(Images_and_videos::class, 'prc_id', 'prc_id');
    }

    public function nices(): HasMany
    {
        return $this->hasMany(Nice::class, 'prc_id', 'prc_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Prc::class, 'parent_id', 'prc_id')
            ->where('type', 1)
            ->latest('created_at')
            ->with('user');
    }
}
