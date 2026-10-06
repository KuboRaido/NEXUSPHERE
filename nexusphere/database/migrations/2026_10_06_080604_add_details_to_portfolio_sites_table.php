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
        Schema::table('portfolio_sites', function (Blueprint $table) {
            // 1つの値
            $table->string('school_name', 30)->nullable();
            $table->string('department', 20)->nullable();
            $table->string('major', 20)->nullable();
            $table->text('job_axis')->nullable();

            // リスト（中身の形・件数・文字数は Service で検査する）
            $table->json('hobbies')->nullable();
            $table->json('life_story')->nullable();
            $table->json('skills')->nullable();
            $table->json('certifications')->nullable();
            $table->json('careers')->nullable();
            $table->json('awards')->nullable();
            $table->json('custom_sections')->nullable();

            // 項目ごとの出す・出さない。キーがない項目は「出す」
            $table->json('visibility')->nullable();

            // 詳細を初めて保存した日時。null の間だけ設定画面に users の値を初期値として表示する
            $table->timestamp('details_saved_at')->nullable();

            // アクセントカラーの名前（blue など）だけを保存する。色の値は保存しない
            $table->string('accent_color', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portfolio_sites', function (Blueprint $table) {
            $table->dropColumn([
                'school_name',
                'department',
                'major',
                'job_axis',
                'hobbies',
                'life_story',
                'skills',
                'certifications',
                'careers',
                'awards',
                'custom_sections',
                'visibility',
                'details_saved_at',
                'accent_color',
            ]);
        });
    }
};
