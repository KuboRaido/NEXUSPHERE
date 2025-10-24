<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['user_name', 'content', 'likes'];

    // コメントとのリレーション
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // 画像とのリレーション
    public function images()
    {
        return $this->hasMany(PostImage::class);
    }
}
