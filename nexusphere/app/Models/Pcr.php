<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pcr extends Model
{
    protected $fillable = ['user_id','sentence','type','parent_id','circle_id'];
}
