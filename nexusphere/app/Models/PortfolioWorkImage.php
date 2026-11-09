<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioWorkImage extends Model
{
    use HasFactory;

    protected $table = 'portfolio_work_images';
    protected $primaryKey = 'portfolio_work_image_id';

    // portfolio_work_id は入れない（下の「fillable について」）
    protected $fillable = [
        'sort_order',
        'path',
    ];

    //DBの値をPHPで扱いやすい形に変換する設定
    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function work()
    {
        return $this->belongsTo(PortfolioWork::class, 'portfolio_work_id', 'portfolio_work_id');
    }
}