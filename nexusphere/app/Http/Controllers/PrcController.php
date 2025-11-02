<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prc;

class PrcController extends Controller
{
    // 投稿一覧
    public function index()
    {
        $posts = Prc::with(['comments', 'images'])->orderBy('created_at', 'desc')->get();
        return view('home', compact('posts'));
    }

    // 投稿作成（テキスト＋画像最大10枚）
    public function store(Request $request)
    {
        // バリデーション
        $request->validate([
            'content' => 'required|string|max:1000',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 1枚最大5MB
        ]);

        // 投稿作成
        $post = Prc::create([
            'user_name' => 'ゲスト', // 仮のユーザー名
            'content' => $request->input('content'),
            'images' => [],
            'likes' => 0,
        ]);

        // 画像保存（最大10枚）
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $images = array_slice($images, 0, 10); // 最大10枚
            foreach ($images as $image) {
                $path = $image->store('post', 'public');
                $post->images()->create([
                    'image_path' => $path
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
        $post->comments()->create([
            'content' => $request->input('comment')
        ]);

        return redirect()->back();
    }

    // いいね
    public function like($postId)
    {
        $post = Prc::findOrFail($postId);
        $post->increment('likes');
        return redirect()->back();
    }

    public function post()
    {
        return view('post');
    }
}
