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
        Schema::create('profiles', function (Blueprint $table) {
            // Laravel migrationではNotNullがデフォルト
            $table->id();
            $table->string('serial_no')->unique();
            $table->string('name');
            $table->integer('age');
            $table->float('height')->nullable();
            $table->float('weight')->nullable();
            $table->date('birth_day')->default('2000-01-01');
            // $table->foreignId('user_id');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
