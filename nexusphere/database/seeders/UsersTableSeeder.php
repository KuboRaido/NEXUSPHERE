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
        'user_id' => 1,
        'mail'    => 'a@sba.ac.jp',
        'password'=> Hash::make('00000000'),
        'name'    => 'a',
        'age'     => 20,
        'grade'   => 3,
        'subject' => 'test',
        'major'   => 'test',
        'icon'    => null,
      ]);

      User::create([
        'user_id' => 2,
        'mail'    => 'b@sba.ac.jp',
        'password'=> Hash::make('00000000'),
        'name'    => 'b',
        'age'     => 20,
        'grade'   => 3,
        'subject' => 'test',
        'major'   => 'test',
        'icon'    => null,
      ]);

      User::create([
        'user_id' => 3,
        'mail'    => 'c@sba.ac.jp',
        'password'=> Hash::make('00000000'),
        'name'    => 'c',
        'age'     => 20,
        'grade'   => 3,
        'subject' => 'test',
        'major'   => 'test',
        'icon'    => null,
      ]);
    }
}
 