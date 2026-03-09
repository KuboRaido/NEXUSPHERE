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
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('mail',255)->unique();
            $table->string('password',255);
            $table->text('name');
            $table->integer('age');
            $table->integer('grade');
            $table->text('subject');
            $table->text('major');
            $table->text('icon')->nullable();
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dms',function(\Illuminate\Database\Schema\Blueprint $table){
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('users');
    }
};
