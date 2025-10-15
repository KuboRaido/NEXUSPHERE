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
        // Neutralized duplicate migration: profiles table is created by earlier migration (2025_06_26_082011_create_profiles_table.php)
        // This migration intentionally does nothing to avoid creating the table twice.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: main profiles migration handles drop.
    }
};
