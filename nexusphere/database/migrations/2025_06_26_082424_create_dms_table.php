<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //Schema::disableForeignKeyConstraints();
        //DB::statement('ALTER TABLE dms MODIFY circle_id BIGINT UNSIGNED NULL');
        //Schema::enableForeignKeyConstraints();

        Schema::create('dms', function (Blueprint $table) {
            
            $table->bigIncrements('dm_id');

            $table->foreignId('circle_id')->nullable()->constrained('circles','circle_id')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users','user_id')->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('groups','group_id')->cascadeOnDelete();

            $table->foreignId('sender_id')->constrained('users','user_id')->cascadeOnDelete();
            $table->foreignId('receiver_id')->nullable()->constrained('users','user_id')->cascadeOnDelete();

            $table->text('message_text')->nullable();
            $table->unsignedBigInteger('conversation_id')->nullable()->index();
            $table->json('attachments')->nullable();

            $table->string('dm_key')->nullable();

            $table->foreignId('parent_id')->nullable()->constrained('dms','dm_id')->nullOnDelete();
            $table->foreignId('reply_to_dm_id')->nullable()->constrained('dms','dm_id')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['circle_id','created_at'],'dm_circle_created_idx');
            $table->index(['user_id','created_at'],'dm_user_created_idx');
            $table->index(['group_id','created_at'],'dm_group_created_idx');
            $table->index(['sender_id','created_at'],'dm_sender_created_idx');
            $table->index(['receiver_id','created_at'],'dm_receiver_created_idx');
            $table->index(['dm_key','created_at'],'dm_dmkey_created_idx');

            $table->index(['sender_id','receiver_id','created_at']);
            $table->index(['receiver_id','sender_id','created_at']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

    // 外部キーを持つカラムを削除してからテーブル削除
    Schema::table('dms', function (Blueprint $table) {
        if (Schema::hasColumn('dms', 'circle_id')) {
            $table->dropForeign(['circle_id']);
        }
        if (Schema::hasColumn('dms', 'user_id')) {
            $table->dropForeign(['user_id']);
        }
        if (Schema::hasColumn('dms', 'group_id')) {
            $table->dropForeign(['group_id']);
        }
        if (Schema::hasColumn('dms', 'sender_id')) {
            $table->dropForeign(['sender_id']);
        }
        if (Schema::hasColumn('dms', 'receiver_id')) {
            $table->dropForeign(['receiver_id']);
        }
        if (Schema::hasColumn('dms', 'parent_id')) {
            $table->dropForeign(['parent_id']);
        }
        if (Schema::hasColumn('dms', 'reply_to_dm_id')) {
            $table->dropForeign(['reply_to_dm_id']);
        }
    });

    Schema::dropIfExists('dms');

    Schema::enableForeignKeyConstraints();
    }
};

