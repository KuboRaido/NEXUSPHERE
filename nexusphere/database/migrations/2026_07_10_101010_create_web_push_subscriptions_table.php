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
        Schema::create('web_push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users','user_id')->onDelete('cascade');
            $table->text('endpoint'); //Pushサービスが発行するURL　長さの上限が保証されていないためtext
            $table->text('p256dh'); //公開鍵
            $table->text('auth'); //認証トークン
            $table->timestamp('last_used_at')->nullable(); //購読の期限切れ管理
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_push_subscriptions');
    }
};
