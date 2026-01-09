<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{

    use HasApiTokens, HasFactory,Notifiable;

    protected $primaryKey = 'user_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = ['name','mail','password','age','grade','subject','major','icon'];
    protected $hidden = ['password','remember_token'];

    public function prcs()
    {
        return $this->hasMany(Prc::class, 'user_id', 'user_id');
    }

    public function circles()
    {
        return $this->belongsToMany(Circle::class,'circle_users', 'user_id', 'circle_id')
                    ->withTimestamps();
    }

    public function getAvatarUrlAttribute(): string
    {
        if(!empty($this->icon)){
            if(Str::startsWith($this->icon,['http://','https://','/'])){
                return $this->icon;
            }
            return Storage::url($this->icon);
        }
        return asset('images/default-avatar.png');
    }

    public function circleRequest(){
        return $this->hasMany(Circle_requests::class,'circle_request_id','user_id');
    }
}
