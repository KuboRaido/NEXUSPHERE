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
        Schema::create('circles', function (Blueprint $table) {
            $table->id('circle_id');
            $table->integer('category')->nullable();
            $table->string('circle_name')->unique();
            $table->integer('owner_id');
            $table->string('sentence',255);
            $table->string('icon')->nullable()->unique();
            $table->integer('members_count')->default(0);
            $table->timestamps();
        });
    }

    /*** Reverse the migrations.*/
    public function down(): void
    {
        Schema::table('dms',function(\Illuminate\Database\Schema\Blueprint $table){
            $table->dropForeign(['circle_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['group_id']);
            $table->dropForeign(['sender_id']);
            $table->dropForeign(['receiver_id']);
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['reply_to_dm_id']);
        });
        Schema::dropIfExists('circles');
    }
};
