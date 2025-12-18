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
        Schema::create('prcs', function (Blueprint $table) {
            $table->id('prc_id');
            $table->foreignId('user_id')->nullable()->constrained('users','user_id')->cascadeOnDelete();
            $table->text('sentence')->nullable();
            $table->foreignId('profile_id')->nullable()->constrained('profiles','profile_id')->cascadeOnDelete();
            $table->integer('type')->nullable();
            $table->integer('parent_id')->nullable();
            $table->foreignId('circle_id')->nullable()->constrained('circles','circle_id')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prcs');
    }
};
