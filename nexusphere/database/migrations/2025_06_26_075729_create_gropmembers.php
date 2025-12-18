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
        Schema::create('groupmembers', function (Blueprint $table) {
            $table->id('groupmember_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('group_id');
            $table->timestamps();

            $table->unique(['user_id', 'group_id']);
            $table->index('group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groupmembers');
    }
};
