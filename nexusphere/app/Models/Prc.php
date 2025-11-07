<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prc extends Model
{
    protected $table = 'prcs';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = ['user_id','sentence','comments','type','parent_id','circle_id','content','image_and_video_id',/*'profile_id'*/];
    protected $primaryKey = 'prc_id';
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function images()
    {
        return $this->hasMany(Images_and_videos::class, 'prc_id', 'prc_id');
    }

    public function nices()
    {
        return $this->hasMany(Nice::class, 'prc_id', 'prc_id');
    }

    public function comments()
    {
        return $this->hasMany(Prc::class, 'parent_id', 'prc_id')->where('type', 'comment')->with('user');
    }
}
