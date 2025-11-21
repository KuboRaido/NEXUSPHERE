<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Images_and_videos;
use App\Models\Prc;
use App\Models\Nice;

class PrcController extends Controller
{
    
    // 投稿一覧
    public function index()
    {
        $posts = Prc::with(['comments', 'images'])
                    ->orderBy('created_at', 'desc')
                    ->get();
        return view('home', compact('posts'));
    }

    // 投稿作成（テキスト＋画像最大10枚）
    public function store(Request $request)
    {

        // バリデーション
        $request->validate([
            'sentence' => 'required|string|max:1000',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 1枚最大5MB
        ]);

        // 投稿作成
        $post = Prc::create([
            'user_id' => Auth::id(),
            'sentence' => $request->input('sentence'),
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
                $path = $image->store('post', 'public');
                // Images_and_videos のカラム名は 'image'
                Images_and_videos::create([
                    'prc_id' => $post->prc_id,
                    'image' => $path,
                ]);
            }
        }

        return redirect()->back();
    }

    // コメント作成
    public function comment(Request $request, $postId)
    {
        $request->validate([
            'comment' => 'required|string|max:500',
        ]);

        $post = Prc::findOrFail($postId);

        // type と user_idを必ずセット
        $post->comments()->create([
            'sentence' => $request->comment,
            'user_id' =>Auth::id(),
            'type'    => '1', //数字に変更
            'parent_id' => $post->prc_id,
        ]);

        return redirect()->back();
    }

    // いいね(POST /posts/{prc_id}/like)
    public function like(Request $request, $postId)
    {
        $userId = Auth::id();
        abort_if(!$userId, 401, 'Unauthenticated');

        $post = Prc::where('prc_id',$postId)->firstOrFail();

        // すでにいいねがあるか確認（トグル）
        $existing = Nice::where('prc_id', $post->prc_id)
                        ->where('user_id', $userId)
                        ->first();

        if ($existing) {
            $existing->delete();
        } else {
            // create new nice
            Nice::create([
                'prc_id' => $post->prc_id,
                'user_id' => $userId,
            ]);
        }

        return redirect()->back();
    }

    public function post()
    {
        return view('post');
    }
}
