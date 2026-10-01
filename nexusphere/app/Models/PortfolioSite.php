<?php

namespace App\Models;

use App\Enums\SiteType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioSite extends Model
{
    use HasFactory;

    protected $table = 'portfolio_sites';
    protected $primaryKey = 'portfolio_site_id';

    protected $fillable = [
        'user_id',
        'token',
        'site_type',
        'external_url',
        'is_public',
        'agreed_at',
        'terms_version',
        'display_name',
        'bio',
        'links',
    ];

    //DBの値をPHPで扱いやすい形に変換する設定
    protected $casts = [
        'site_type' => SiteType::class,
        'is_public' => 'boolean',
        'agreed_at' => 'datetime',
        'links'     => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}