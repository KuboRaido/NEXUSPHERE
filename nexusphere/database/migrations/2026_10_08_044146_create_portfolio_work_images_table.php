<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('portfolio_work_images', function (Blueprint $table) {
            $table->id('portfolio_work_image_id');
            // どの制作物の画像か。制作物が消えたら一緒に消える（画像ファイルは消えないので Service で消す）
            $table->foreignId('portfolio_work_id')->constrained('portfolio_works', 'portfolio_work_id')->cascadeOnDelete();
            // 並び順。0 がカードに出る1枚目
            $table->unsignedSmallInteger('sort_order')->default(0);
            // 保存したファイルの場所（ディスクの中の相対パス）。縮小と位置情報の削除を済ませたものだけを入れる
            $table->string('path', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_work_images');
    }
};