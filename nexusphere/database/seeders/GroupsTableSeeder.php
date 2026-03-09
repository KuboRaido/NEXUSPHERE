<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class GroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('circles')->insert([
            [
                'circle_id'   => 1,
                'circle_name' => 'AI研究部',
                'category'    => '研究',
                'owner_id'    => 2,
                'sentence'    => 'AIに関する研究を行うサークルです。',
                'icon'        => 'ai_circle.png',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'circle_id'   => 2,
                'circle_name' => '音楽制作サークル',
                'category'    => '音楽',
                'owner_id'    => 2,
                'sentence'    => '音楽制作に関するサークルです。',
                'icon'        => 'music_circle.png',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'circle_id'   => 3,
                'circle_name' => 'ゲーム開発同好会',
                'category'    => 'ゲーム',
                'owner_id'    => 2,
                'sentence'    => 'ゲーム開発に興味のある人の集まりです。',
                'icon'        => 'game_circle.png',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
