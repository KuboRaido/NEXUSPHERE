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
        'user_id'             => 10,
        'mail'                => 'a@sba.ac.jp',
        'password'            => Hash::make('00000000'),
        'name'                => 'a',
        'age'                 => 20,
        'grade'               => 3,
        'subject'             => 'test',
        'major'               => 'test',
        'icon'                => null,
        'email_verified_at'   => '2026-01-15 02:48:25',
      ]);

      User::create([
        'user_id' => 11,
        'mail'    => 'b@sba.ac.jp',
        'password'=> Hash::make('00000000'),
        'name'    => 'b',
        'age'     => 20,
        'grade'   => 3,
        'subject' => 'test',
        'major'   => 'test',
        'icon'    => null,
        'email_verified_at'   => '2026-01-15 02:48:25',
      ]);

      User::create([
        'user_id' => 12,
        'mail'    => 'c@sba.ac.jp',
        'password'=> Hash::make('00000000'),
        'name'    => 'c',
        'age'     => 20,
        'grade'   => 3,
        'subject' => 'test',
        'major'   => 'test',
        'icon'    => null,
        'email_verified_at'   => '2026-01-15 02:48:25',
      ]);
    }
}
 