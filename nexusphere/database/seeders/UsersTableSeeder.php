<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
      User::create([
        'user_id'             => 100,
        'mail'                => 'a@sba.ac.jp',
        'password'            => Hash::make('00000000'),
        'name'                => 'Guest1',
        'job'                 => '講師',
        'icon'                => null,
        'email_verified_at'   => '2026-01-15 02:48:25',
      ]);

      User::create([
        'user_id' => 101,
        'mail'    => 'b@sba.ac.jp',
        'password'=> Hash::make('00000000'),
        'name'    => 'Guest2',
        'job'     => '教員',
        'icon'    => null,
        'email_verified_at'   => '2026-01-15 02:48:25',
      ]);
    }
}