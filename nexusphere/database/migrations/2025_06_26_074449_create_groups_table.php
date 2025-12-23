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
        Schema::create('groups', function (Blueprint $table) {
            $table->id('group_id');
            $table->string('group_name')->unique();
            $table->foreignId('circle_id')->nullable()->constrained('circles','circle_id')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dms',function(\Illuminate\Database\Schema\Blueprint $table){
            $table->dropForeign(['group_id']);
        });
        Schema::dropIfExists('groups');
    }
};
