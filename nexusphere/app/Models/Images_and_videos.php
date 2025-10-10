<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Dm;
class Images_and_videos extends Model
{
    protected $table = 'images_and_videos';
    protected $primaryKey = 'image_and_video_id';
    protected $fillable = ['prc_id','video','image','dm_id'];
    protected $appends = ['url','type'];
    public $timestamps = true;
    
    public function getUrlAttribute(){
        $path = $this->image ?? $this->video;
        return $path ? Storage::url($path) : null;
    }

    public function getTypeAttribute(){
        if ($this->image) return 'image';
        if ($this->video) return 'video';
        return 'file';
    }

    public function message(){
        return $this->belongsTo(Dm::class, 'dm_id','dm_id');
    }
}
