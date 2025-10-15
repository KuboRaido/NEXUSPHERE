<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pcr extends Model
{
    protected $table = 'prcs';
    protected $fillable = ['user_id','sentence','type','parent_id','circle_id','content'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
