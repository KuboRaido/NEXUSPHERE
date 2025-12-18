<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //各テーブルへのデータの流し込みを呼び出す
        $this->call(UsersTableSeeder::class);
        $this->call(GroupsTableSeeder::class);
    }
}
