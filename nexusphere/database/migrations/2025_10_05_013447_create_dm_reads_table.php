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
        Schema::create('dm_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users','user_id')->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('users','user_id')->cascadeOnDelete();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'partner_id']); // ← upsert の衝突キー
            $table->index(['partner_id', 'user_id']);  // 集計用
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dm_reads');
    }
};
