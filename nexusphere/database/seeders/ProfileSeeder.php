<?php

namespace Database\Seeders;

use App\Models\Profile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //テーブルのレコードを削除
        Profile::truncate();

        //プロフィールクラスのインスタンスを生成
        $profile = new Profile();
        $profile->serial_no = 'ABC123';
        $profile->name='okita';
        $profile->age = 33;
        $profile->height = 165.5;
        $profile->weight = 60.8;
        $profile->birth_day = '1992-11-19';
        $profile->save();
    }
}
