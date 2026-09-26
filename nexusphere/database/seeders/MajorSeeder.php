<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Major;
use App\Models\Subject;


class MajorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $majorData = [
            'AI&テクノロジー科' => [
                "AIエンジニア専攻",
                "ホワイトハッカー専攻",
                "スポーツテック&AI専攻",
                "生成AIクリエーター専攻",
                "ITプログラマー専攻",
                "スーパーゲームクリエーター専攻",
                "e-sportsマネジメント専攻",
                "ゲームキャラクター＆マネジメント専攻",
                "スーパーCG動画クリエーター専攻",
                "イラスト&SCGグラフィック専攻",
            ],
            "デジタルテクノロジー科" => [
                "ITプログラマー専攻",
                "e-sportプロゲーマー専攻",
                "ゲーム実況＆ストリーマー専攻",
                "ゲームプログラマー専攻",
            ],
            "クリエイティブデザイン科" => [
                "動画クリエーター専攻",
                "CGアニメーション専攻",
                "コミックイラスト＆マンガ専攻",
            ],
        ];

        foreach ($majorData as $subjectName => $majorNames){
            $subject = Subject::where('subject_name',$subjectName)->first();
            if($subject){
                foreach($majorNames as $majorName){
                    Major::create([
                        'subject_id' => $subject->subject_id,
                        'major_name' => $majorName,
                    ]);
                };
            }
        }
    }
}
