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
        Schema::table('users', function (Blueprint $table) {
            //job カラム追加
            $table->string('job')->default('学生');

            // 既存の subject カラムを nullable に変更
            // change() をつけるのがポイントです
            $table->integer('grade')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // job カラムを削除
            $table->dropColumn('job');

            // subject カラムを元に戻す（nullableを外す）
            // nullable(false) または単に nullable() を書かずに change() します
            $table->integer('grade')->nullable(false)->change();
        });
    }
};