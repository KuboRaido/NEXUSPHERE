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
            $table->integer('user_id')->unique();
            $table->text('sentence')->nullable();
            $table->foreignId('profile_id')->constrained('profiles','profile_id')->cascadeOnDelete();
            $table->integer('type');
            $table->integer('parent_id');
            $table->integer('circle_id');
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
