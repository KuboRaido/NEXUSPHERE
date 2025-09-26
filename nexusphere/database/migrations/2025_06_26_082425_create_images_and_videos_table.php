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
        Schema::create('images_and_videos', function (Blueprint $table) {
            $table->id('image_and_video_id');
            $table->integer('prc_id')->unique();
            $table->text('movie')->nullable();
            $table->text('image')->nullable();
            $table->integer('dm_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images_and_videos');
    }
};
