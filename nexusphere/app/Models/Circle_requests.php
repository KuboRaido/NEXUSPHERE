<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Circle_requests extends Model
{
    protected $table = 'circle_requests';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = ['circle_request_id','circle_id','user_id','status','request_at'];
    protected $primaryKey = 'circle_request_id';

    public function getRouteKeyName()
    {
        return 'circle_request_id';
    }

    public function circle(){
        return $this->belongsTo(Circle::class,'circle_id','circle_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id','user_id');
    }
}
