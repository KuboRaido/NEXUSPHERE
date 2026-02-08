<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Images_and_videos;
use App\Models\Prc;
use App\Models\Nice;
use App\Rules\NgWord;

class PrcController extends Controller
{
    // 投稿一覧（検索対応）
    public function index(Request $request)
    {
        if(!empty($request->circle_id)){
        // 投稿(type = 0) をベースにクエリ作成
        $query = Prc::where('type', 0)
                    ->with(['comments', 'images'])
                    ->orderBy('created_at', 'desc');

        $posts = $query->get();

        return view('circleProfile', compact('posts'));
        }

        if(empty($request -> circle_id)){
            // 投稿(type = 0) をベースにクエリ作成
            $query = Prc::where('type', 0)
                    ->whereNull('circle_id')
                    ->with(['comments', 'images'])
                    ->orderBy('created_at', 'desc');

            // 検索ワードがある場合は sentence に対して LIKE 検索
            if ($request->filled('search')) {
                $keyword = $request->input('search');
                $query->where('sentence', 'LIKE', "%{$keyword}%");
            }

            $posts = $query->get();

            return view('home', compact('posts'));
        }
    }

    // 投稿作成（テキスト＋画像最大10枚）
    public function store(Request $request)
    {
        // バリデーション
        $request->validate([
            'sentence' => ['required','string','max:1000',new NgWord],
            'images.*' => ['image','max:1024'], // 1枚最大5MB
            'videos.*' => ['mimetypes:video/mp4,video/quicktime','max:51200'],
        ]);

        // 投稿作成（type:0 = 投稿）
            $post = Prc::create([
            'user_id' => Auth::id(),
            'sentence' => $request->input('sentence'),
            'type' => 0,
            ]);
        
        // 画像保存（最大10枚）
        $fileInputs = [];
        if ($request->hasFile('images')) {
            $fileInputs = $request->file('images');
        }

        if (!empty($fileInputs)) {
            $images = is_array($fileInputs) ? $fileInputs : [$fileInputs];
            $images = array_slice($images, 0, 10);

            foreach ($images as $image) {
                    $path = $image->store('', 'post');
                    // Images_and_videos のカラム名は 'image'
                    Images_and_videos::create([
                    'prc_id' => $post->prc_id,
                    'image' => $path,
                    ]);
            }
        }

        if ($request->hasFile('videos')) {
            $videos = $request->file('videos');

            foreach ($videos as $video) {
                $path = $video->store('', 'post');

                Images_and_videos::create([
                    'prc_id' => $post->prc_id,
                    'video'  => $path,
                ]);
            }
        }

        return redirect()->back();
    }

    public function circleStore(Request $request){

        $circle = $request->circle_id;
        // バリデーション
        $request->validate([
            'sentence' => ['required','string','max:1000',new NgWord],
            'images.*' => ['image','max:5120'], // 1枚最大5MB
            'videos.*' => ['mimetypes:video/mp4,video/quicktime','max:512000'],
        ]);

        // 投稿作成（type:0 = 投稿）
            $post = Prc::create([
            'user_id' => Auth::id(),
            'sentence' => $request->input('sentence'),
            'circle_id' => $request->input('circle_id'),
            'type' => 0,
        ]);
        
        // 画像保存（最大10枚）
        $fileInputs = [];
        if ($request->hasFile('images')) {
            $fileInputs = $request->file('images');
        }

        if (!empty($fileInputs)) {
            $images = is_array($fileInputs) ? $fileInputs : [$fileInputs];
            $images = array_slice($images, 0, 10);
            foreach ($images as $image) {
                    $path = $image->store('', 'post');
                    // Images_and_videos のカラム名は 'image'
                    Images_and_videos::create([
                        'prc_id' => $post->prc_id,
                        'image' => $path,
                        'circle_id' => $request->circle_id,
                    ]);
            }
        }

        if ($request->hasFile('videos')) {
            $videos = $request->file('videos');

            foreach ($videos as $video) {
                    $path = $video->store('', 'post');

                    Images_and_videos::create([
                        'prc_id' => $post->prc_id,
                        'video'  => $path,
                        'circle_id' => $request->circle_id,
                    ]);
            }
        }

        return redirect()->route('circle.profile',['circle' => $circle]);
    }
    // コメント作成
    public function comment(Request $request, $postId)
    {
        $request->validate([
            'comment' => ['required','string','max:500',new NgWord]
        ]);

        $post = Prc::findOrFail($postId);

        $post->comments()->create([
            'sentence' => $request->comment,
            'user_id'  => Auth::id(),
            'type'     => 1,  // コメントを示す数値
        ]);

        return redirect()->back();
    }

    // いいね(POST /posts/{prc_id}/like)
    public function like($postId)
    {
        $userId = Auth::id();
        abort_if(!$userId, 401, 'Unauthenticated');

        // 投稿取得
        $post = Prc::where('prc_id', $postId)->firstOrFail();

        // 既存のいいねを探す
        $existing = Nice::where('prc_id', $post->prc_id)
                        ->where('user_id', $userId)
                        ->first();

        if ($existing) {
            // ❌ すでに存在 → 削除（いいね解除）
            $existing->delete();
        } else {
            // ❤️ 存在しない → 新規作成
            Nice::firstOrCreate([
                'prc_id' => $post->prc_id,
                'user_id' => $userId,
            ]);
        }

        return redirect()->back();
    }


    // 投稿フォーム表示
    public function post()
    {
        $userId = Auth::id();
        abort_if(!$userId, 401);

        $post = Auth::user();
        echo "<script>console.log(" . json_encode($post) . ");</script>";
        return view('post',['post' => $post]);
    }
}
