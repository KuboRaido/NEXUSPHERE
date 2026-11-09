<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run():void
    {
          $users = [
            [
              'user_id'             => 1,
              'mail'                => 'a@sba.ac.jp',
              'password'            => Hash::make('00000000'),
              'name'                => 'Guest1',
              'job'                 => '講師',
              'subject'             => 'AI＆テクノロジー科',
              'major'               => 'AIエンジニア専攻',
              'icon'                => null,
              'email_verified_at'   => '2026-01-15 02:48:25',
            ],
            [
              'user_id'             => 2,
              'mail'                => 'b@sba.ac.jp',
              'password'            => Hash::make('00000000'),
              'name'                => 'Guest2',
              'job'                 => '講師',
              'subject'             => 'AI＆テクノロジー科',
              'major'               => 'ITプログラマー専攻',
              'icon'                => null,
              'email_verified_at'   => '2026-01-15 02:49:25',
            ],
            [
              'user_id'             => 3,
              'mail'                => 'c@sba.ac.jp',
              'password'            => Hash::make('00000000'),
              'name'                => 'Guest3',
              'job'                 => '講師',
              'subject'             => 'AI&テクノロジー科',
              'major'               => 'ホワイトハッカー専攻',
              'icon'                => null,
              'email_verified_at'   => '2026-01-15 02:50:25',
            ],
            [
              'user_id'             => 4,
              'mail'                => 'd@sba.ac.jp',
              'password'            => Hash::make('00000000'),
              'name'                => 'Guest4',
              'job'                 => '講師',
              'subject'             => 'AI&テクノロジー科',
              'major'               => 'スポーツテック&AI専攻',
              'icon'                => null,
              'email_verified_at'   => '2026-01-15 02:51:25',
            ],
        ];

        User::insert($users);

    }
}