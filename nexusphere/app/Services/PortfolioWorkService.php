<?php

namespace App\Services;

use DomainException;
use Throwable;
use App\Models\PortfolioSite;
use App\Models\PortfolioWork;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Exceptions\DecoderException;
use Intervention\Image\ImageManager;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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

        $imageManager = ImageManager::gd(autoOrientation: true, strip: true);
        $results = [];
        foreach($newImages as $slot => $newImage){
            try{
                $image = $imageManager->read($newImage);
                $scale = $image->scaleDown(width: 1600, height: 1600);
                $toWebp = $scale->toWebp(quality: 75);
                $results[$slot] = $toWebp;
            } catch(DecoderException $e) {
                throw ValidationException::withMessages([
                    'new_images.' . $slot => '画像の' . ($slot + 1) . '枚目：読み込めませんでした。別の画像を選んでください。',
                ]);
            }
        }

        $resultPath = [];
        foreach($results as $slot => $result){
            $path        = $site->token."/".Str::random(40).'.webp';
            $trueOrFalse = Storage::disk('works')->put($path,$result->toString());
            if($trueOrFalse === false){
                Storage::disk('works')->delete($resultPath);
                Log::error('画像の保存に失敗しました。',['portfolio_site_id' => $site->portfolio_site_id, 'slot' => $slot]);
                throw new DomainException('画像の保存に失敗しました。時間をおいてもう一度保存してください。');
            }
            $resultPath[$slot] = $path;
        }

        $fields = Arr::except($validated, ['new_images', 'delete_images']);

        try {
            DB::transaction(function () use ($site, $work, $fields, $imagesToDelete, $resultPath) {
                if ($work === null) {
                    $sortOrder = ($site->works()->max('sort_order') ?? -1) + 1;
                    $work = $site->works()->create([...$fields, 'sort_order' => $sortOrder]);
                } else {
                    $work->update($fields);
                }

                $work->images()->whereIn('portfolio_work_image_id', $imagesToDelete->pluck('portfolio_work_image_id'))->delete();

                foreach ($resultPath as $slot => $path) {
                    $work->images()->create(['sort_order' => $slot, 'path' => $path]);
                }
            });
        } catch (Throwable $e) {
            Storage::disk('works')->delete(array_values($resultPath));
            throw $e;
        }

        Storage::disk('works')->delete($imagesToDelete->pluck('path')->all());
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
        'new_images.array'      => '画像の枠の指定が正しくありません。ページを読み込み直してください。',
        'new_images.*.file'     => '画像の:position枚目：アップロードに失敗しました。もう一度選んでください。',
        'new_images.*.mimes'    => '画像の:position枚目：JPEG・PNG・WebP の画像を選んでください。',
        'new_images.*.max'      => '画像の:position枚目：10MB以下の画像を選んでください。',
        'new_images.*.dimensions' => '画像の:position枚目：縦横8192px以下の画像を選んでください。',
        'delete_images.*.integer' => '消す画像の指定が正しくありません。ページを読み込み直してください。',
    ];
}