<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prc extends Model
{
    protected $table = 'prcs';
    protected $fillable = ['user_id','sentence','type','parent_id','circle_id','content','image_and_video_id','profile_id'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
