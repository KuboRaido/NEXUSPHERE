<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Circle extends Model
{
    use HasFactory;

    protected $table = 'circles';
    protected $primaryKey = 'circle_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'circle_id',
        'circle_name',
        'category',
        'sentence',
        'icon',
        'owner_id',
        'members_count',
    ];

    public function getRouteKeyName(): string
    {
        return 'circle_id';
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'circle_users', 'circle_id', 'user_id')
            ->withTimestamps();
    }

    public function joinRequests(): HasMany
    {
        return $this->hasMany(Circle_requests::class, 'circle_id', 'circle_id');
    }
}
