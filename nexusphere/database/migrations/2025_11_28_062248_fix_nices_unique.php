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
        Schema::table('nices', function (Blueprint $table) {
            // (1) 既存の user_id の UNIQUE 制約を削除
            $table->dropUnique('nices_user_id_unique');

            // (2) prc_id × user_id の複合 UNIQUE 制約を追加
            $table->unique(['prc_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nices', function (Blueprint $table) {
            // 元に戻す：複合UNIQUEを削除し、user_id単体のUNIQUEを復元
            $table->dropUnique(['prc_id', 'user_id']);
            $table->unique('user_id');
        });
    }
};
