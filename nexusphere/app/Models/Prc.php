<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prc extends Model
{
    protected $table = 'prcs';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'sentence',
        'comments',
        'type',
        'parent_id',
        'circle_id',
        'content',
        'image_and_video_id',
    ];

    protected $primaryKey = 'prc_id';

    // 投稿したユーザー
    public function user()
    {
        return $this->belongsTo(User::class,'user_id','user_id');
    }

    public function circle()
    {
        return $this->belongsTo(Circle_user::class,'circle_id','circle_id');
    }

    // 画像
    public function images()
    {
        return $this->hasMany(Images_and_videos::class, 'prc_id', 'prc_id');
    }

    // いいね
    public function nices()
    {
        return $this->hasMany(Nice::class, 'prc_id', 'prc_id');
    }

    // ★ コメント（超重要！これが無いせいでエラー発生していた）
    public function comments()
    {
        return $this->hasMany(Prc::class, 'parent_id', 'prc_id')
                    ->where('type', 1)
                    ->with('user');
    }
}
