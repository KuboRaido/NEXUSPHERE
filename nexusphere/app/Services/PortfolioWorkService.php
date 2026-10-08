<?php

namespace App\Services;

use App\Models\PortfolioSite;
use App\Models\PortfolioWork;
use DomainException;
use Illuminate\Support\Facades\Validator;

// 制作物（portfolio_works）と画像（portfolio_work_images）を、決めたルールどおりに保存・削除するための処理
class PortfolioWorkService
{
    // 1人あたりの制作物の上限
    public const WORKS_MAX_COUNT = 10;

    // 1つの制作物あたりの画像の枠の数（枠の番号は 0〜9）
    public const IMAGES_MAX_COUNT = 10;

    // 画像を保存するディスク（config/filesystems.php の works）
    private const DISK = 'works';

    // 縮小したあとの長辺（px）
    private const IMAGE_LONG_EDGE = 1600;

    public function save(PortfolioSite $site, ?PortfolioWork $work, array $input, array $newImages): void
    {
        $validated = Validator::make([...$input, 'new_images' => $newImages], self::WORK_RULES, self::WORK_MESSAGES)->validate();

        if(!$work && $site->works()->count() >= self::WORKS_MAX_COUNT){
            throw new DomainException('制作物は10件まで登録可能です。');
        }

        $deleteIds = array_unique($validated['delete_images'] ?? []);

        $imagesToDelete = collect();

        if($deleteIds !== []){
            if($work === null) {
                throw new DomainException('消す画像の指定が正しくありません。ページを読み込み直してください。');
            }

            $imagesToDelete = $work->images()->whereIn('portfolio_work_image_id', $deleteIds)->get();

            if($imagesToDelete->count() !== count($deleteIds)) {
                throw new DomainException('消す画像の指定が正しくありません。ページを読み込み直してください。');
            }
        }

        if($newImages !== [] && $work !== null){
            $slots = array_keys($newImages);
            $imagesToDelete = $imagesToDelete->merge($work->images()->whereIn('sort_order', $slots)->get())->unique('portfolio_work_image_id');
        }
        
    }
    /**
     * 制作物1件分の入力のルール。
     * フォームのキー：title, summary, tech_stack[], url, team_role, why_built, why_tech,
     * hardest_part, own_ideas, current_status, new_images[]（新しく選んだ画像）, delete_images[]（消す画像の ID）
     * 件数の合計（制作物10件・画像10枚）と、delete_images の ID がこの制作物の画像か、はルールでは確かめられないので Service の手順で確かめる。
     */
    private const WORK_RULES = [
        // どれか1つでも入っていたら、タイトルは必須（「これがあるならこれも必要」）
        'title'            => ['nullable', 'string', 'max:50', 'required_with:summary,tech_stack,url,team_role,why_built,why_tech,hardest_part,own_ideas,current_status,new_images'],

        // カードに出す欄
        'summary'          => ['nullable', 'string', 'max:100'],
        'tech_stack'       => ['nullable', 'array', 'max:10'],
        'tech_stack.*'     => ['nullable', 'string', 'max:30'],
        'url'              => ['nullable', 'string', 'max:255', 'url:https'],

        // 押したときに開く欄
        'team_role'        => ['nullable', 'string', 'max:50'],
        'why_built'        => ['nullable', 'string', 'max:500'],
        'why_tech'         => ['nullable', 'string', 'max:500'],
        'hardest_part'     => ['nullable', 'string', 'max:500'],
        'own_ideas'        => ['nullable', 'string', 'max:500'],
        'current_status'   => ['nullable', 'string', 'max:500'],

        // 画像：中身から判定した形式が jpg・png・webp のものだけ、1枚10MB（10240KB）まで、縦横どちらも 8192px まで
        'new_images'       => ['nullable', 'array:0,1,2,3,4,5,6,7,8,9'],
        'new_images.*'     => ['file', 'mimes:jpg,jpeg,png,webp', 'max:10240', 'dimensions:max_width=8192,max_height=8192'],
        'new_images.array' => '画像の枠の指定が正しくありません。ページを読み込み直してください。',
        // 消す画像の ID
        'delete_images'    => ['nullable', 'array'],
        'delete_images.*'  => ['integer'],
    ];

    // エラー文。行のある欄は :position（1から数えた番号）で何個目かを出す
    private const WORK_MESSAGES = [
        'title.required_with'   => 'タイトルを入力してください。',
        'title.max'             => 'タイトルは50文字以内で入力してください。',
        'summary.max'           => '説明は100文字以内で入力してください。',
        'tech_stack.max'        => '使った技術は10個までです。',
        'tech_stack.*.max'      => '使った技術の:position個目：30文字以内で入力してください。',
        'url.max'               => 'URLは255文字以内で入力してください。',
        'url.url'               => 'URLは https:// から始まる形で入力してください。',
        'team_role.max'         => 'チームと担当は50文字以内で入力してください。',
        'why_built.max'         => '「なぜ作ったか」は500文字以内で入力してください。',
        'why_tech.max'          => '「なぜその技術を選んだか」は500文字以内で入力してください。',
        'hardest_part.max'      => '「技術的に苦労したところ」は500文字以内で入力してください。',
        'own_ideas.max'         => '「担当として工夫したこと」は500文字以内で入力してください。',
        'current_status.max'    => '「現在どうなっているか」は500文字以内で入力してください。',
        'new_images.max'        => '画像は1つの制作物につき10枚までです。',
        'new_images.*.file'     => '画像の:position枚目：アップロードに失敗しました。もう一度選んでください。',
        'new_images.*.mimes'    => '画像の:position枚目：JPEG・PNG・WebP の画像を選んでください。',
        'new_images.*.max'      => '画像の:position枚目：10MB以下の画像を選んでください。',
        'new_images.*.dimensions' => '画像の:position枚目：縦横8192px以下の画像を選んでください。',
        'delete_images.*.integer' => '消す画像の指定が正しくありません。ページを読み込み直してください。',
    ];
}