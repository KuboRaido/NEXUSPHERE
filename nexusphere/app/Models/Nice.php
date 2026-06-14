<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nice extends Model
{
    protected $table = 'nices';
    protected $primaryKey = 'nice_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['prc_id', 'user_id'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Prc::class, 'prc_id', 'prc_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
