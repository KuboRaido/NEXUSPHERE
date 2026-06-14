<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Circle_user extends Model
{
    use HasFactory;

    protected $table = 'circle_users';
    protected $primaryKey = 'circle_user_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'circle_user_id',
        'circle_id',
        'user_id',
        'role',
    ];
}
