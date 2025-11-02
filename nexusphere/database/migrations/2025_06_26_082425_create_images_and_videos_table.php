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
            $table->unsignedBigInteger('prc_id');
            $table->text('video')->nullable();
            $table->text('image')->nullable();
            $table->unsignedBigInteger('dm_id')->nullable();
            $table->timestamps();

            $table->foreign('dm_id')->references('dm_id')->on('dms')->cascadeOnDelete();
            $table->index(['prc_id', 'dm_id']);
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
