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
        Schema::create('nices', function (Blueprint $table) {
            $table->id('nice_id');
            $table->integer('prc_id');
            $table->integer('user_id');
            $table->timestamps();

            $table->unique(['prc_id', 'user_id']);
        });
    }
};
