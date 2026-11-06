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
        Schema::create('portfolio_works', function (Blueprint $table) {
            $table->id('portfolio_work_id');
            // どの学生の公開サイトの制作物か。公開サイトが消えたら一緒に消える（画像ファイルは消えないので Service で消す）
            $table->foreignId('portfolio_site_id')->constrained('portfolio_sites', 'portfolio_site_id')->cascadeOnDelete();
            // 並び順。0 が一番上（公開ページで大きく出る1件目）。Service が保存のたびに 0,1,2… を振る
            $table->unsignedSmallInteger('sort_order')->default(0);

            // カードに出す欄（件数・文字数・形は Service で検査する）
            $table->string('title', 50)->nullable();        // タイトル
            $table->string('summary', 100)->nullable();     // 説明（要約）
            $table->json('tech_stack')->nullable();         // 使った技術（10個・1つ30文字）
            $table->string('url', 255)->nullable();         // URL（https だけ）

            // 押したときに開く欄
            $table->string('team_role', 50)->nullable();    // チームと担当
            $table->text('why_built')->nullable();          // なぜ作ったか（500文字）
            $table->text('why_tech')->nullable();           // なぜその技術を選んだか（500文字）
            $table->text('hardest_part')->nullable();       // 技術的に苦労したところ（500文字）
            $table->text('own_ideas')->nullable();          // 担当として工夫したこと（500文字）
            $table->text('current_status')->nullable();     // 現在どうなっているか（500文字）

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_works');
    }
};