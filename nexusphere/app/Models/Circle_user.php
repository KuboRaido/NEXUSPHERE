<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Circle_user extends Model
{
    protected $table = 'circle_users';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = ['circle_user_id','circle_id','user_id'];
    protected $primaryKey = 'circle_user_id';
}
