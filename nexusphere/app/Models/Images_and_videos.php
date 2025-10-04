<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class Images_and_video extends Model
{
    protected $table = 'images_and_videos_table';
    protected $primaryKey = 'images_and_videos_id';
    protected $fillable = ['prc_id','movie','image','dm_id'];

    protected $appends = ['url','type'];

    public function getUrlAttribute(){
        $path = $this->image ?? $this->movie;
        return $path ? Storage::disk('public')->url($path) : null;
    }

    public function getTypeAttribute(){
        return $this->image ? 'image' : ($this->movie ? 'video' : null);
    }

    public function message(){
        return $this->belongsTo(DM::class, 'dm_id','dm_id');
    }
}
