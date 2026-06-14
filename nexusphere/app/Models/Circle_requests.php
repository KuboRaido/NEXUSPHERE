<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Circle_requests extends Model
{
    protected $table = 'circle_requests';
    protected $primaryKey = 'circle_request_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'circle_request_id',
        'circle_id',
        'user_id',
        'status',
        'request_at',
    ];

    public function getRouteKeyName(): string
    {
        return 'circle_request_id';
    }

    public function circle(): BelongsTo
    {
        return $this->belongsTo(Circle::class, 'circle_id', 'circle_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
