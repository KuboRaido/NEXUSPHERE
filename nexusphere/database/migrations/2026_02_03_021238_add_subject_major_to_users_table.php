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
        Schema::table('users', function (Blueprint $table) {
            $table->text('subject')->nullable()->change();
            $table->text('major')->nullable()->change();
            $table->dropColumn('age');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('subject')->nullable(false)->change();
            $table->text('major')->nullable(false)->change();
            $table->integer('age')->required();
        });
    }
};
