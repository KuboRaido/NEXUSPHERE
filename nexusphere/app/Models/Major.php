<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Major extends Model
{
    protected $primaryKey = 'major_id';
    public $timestamps = false;

    public function major(){
        return $this->belongsTo(Subject::class,'subject_id','subject_id');
    }

    public function users(){
        return $this->hasMany(User::class,'major_id','major_id');
    }
}
