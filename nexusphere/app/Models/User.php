<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'user_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'mail',
        'password',
        'age',
        'grade',
        'subject',
        'major',
        'icon',
        'job',
    ];

    protected $hidden = ['password', 'remember_token'];

    public function prcs(): HasMany
    {
        return $this->hasMany(Prc::class, 'user_id', 'user_id');
    }

    public function circles(): BelongsToMany
    {
        return $this->belongsToMany(Circle::class, 'circle_users', 'user_id', 'circle_id')
            ->withTimestamps();
    }

    public function getAvatarUrlAttribute(): string
    {
        if (empty($this->icon)) {
            return asset('images/default-avatar.png');
        }

        if (Str::startsWith($this->icon, ['http://', 'https://', '/'])) {
            return $this->icon;
        }

        return asset('storage/icons/' . $this->icon);
    }

    public function circleRequest(): HasMany
    {
        return $this->hasMany(Circle_requests::class, 'user_id', 'user_id');
    }

    public function major(){
        return $this->belongsTO(Major::class,'major_id','major_id');
    }

    public function getFullMajorAttribute(){
        if(!$this->major) return null;
        return $this->major->subject->subject_name . ' ' . $this->major->major_name;
    }
}
