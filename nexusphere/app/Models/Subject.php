<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $primaryKey = 'subject_id';
    public $timestamps = false;
    protected $fillable = ['subject_name'];
    public function majors(){
        return $this->hasMany(Major::class, 'subject_id', 'subject_id');
    }
}
