<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Groupmember extends Model
{
    protected $table = 'groupmembers';
    protected $primaryKey = 'groupmember_id';
    protected $fillable = ['groupmemmber_id','user_id','group_id'];
}
