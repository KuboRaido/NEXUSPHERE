<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $table = 'profiles';
    protected $primaryKey = 'profile_id';

    protected $fillable = ['user_id'];
}
