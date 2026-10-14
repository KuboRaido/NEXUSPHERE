<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebPushSubscription extends Model
{
    protected $fillable = ['user_id','endpoint','p256dh','auth','last_used_at'];

    protected $casts = [
        'p256dh'       => 'encrypted',
        'auth'         => 'encrypted',
        'last_used_at' => 'datetime',
    ];

    public function user(){
        return $this->belongsTo(User::class,'user_id','user_id');
    }
}
