<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioWork extends Model
{
    use HasFactory;

    protected $table = 'portfolio_works';
    protected $primaryKey = 'portfolio_work_id';

    // portfolio_site_id は入れない（下の「fillable について」）
    protected $fillable = [
        'sort_order',
        'title',
        'summary',
        'tech_stack',
        'url',
        'team_role',
        'why_built',
        'why_tech',
        'hardest_part',
        'own_ideas',
        'current_status',
    ];

    //DBの値をPHPで扱いやすい形に変換する設定
    protected $casts = [
        'sort_order' => 'integer',
        'tech_stack' => 'array',
    ];

    public function site()
    {
        return $this->belongsTo(PortfolioSite::class, 'portfolio_site_id', 'portfolio_site_id');
    }

    // 画像は並び順どおりに取り出す。first() がカードに出る1枚目
    public function images()
    {
        return $this->hasMany(PortfolioWorkImage::class, 'portfolio_work_id', 'portfolio_work_id')
            ->orderBy('sort_order');
    }
}