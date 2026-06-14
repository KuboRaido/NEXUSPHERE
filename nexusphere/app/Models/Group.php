<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Group extends Model
{
    protected $table = 'groups';
    protected $primaryKey = 'group_id';

    protected $fillable = [
        'group_id',
        'group_name',
        'circle_id',
        'members_count',
        'icon',
    ];

    public function circle(): BelongsTo
    {
        return $this->belongsTo(Circle::class, 'circle_id', 'circle_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'groupmembers', 'group_id', 'user_id')
            ->withTimestamps();
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Dm::class, 'group_id', 'group_id')
            ->latestOfMany('dm_id', 'max');
    }
}
