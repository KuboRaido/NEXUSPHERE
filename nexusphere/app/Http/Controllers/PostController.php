<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostImage;

class PostController extends Controller
{
    /**
     * 投稿一覧表示
     */
    public function index()
    {
        $posts = Post::with(['comments', 'images'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('feed', compact('posts'));
    }

    /**
     * 投稿作成（テキスト＋画像最大10枚）
     */
    public function store(Request $request)
    {
        // バリデーション
        $request->validate([
            'content' => 'required|string|max:1000',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 各画像最大5MB
        ]);

        // 投稿作成
        $post = Post::create([
            'user_name' => 'ゲスト', // 仮ユーザー名（ログイン機能導入後にAuthへ変更）
            'content' => $request->input('content'),
            'likes' => 0,
        ]);

        // 画像保存（最大10枚）
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $images = array_slice($images, 0, 10); // 最大10枚制限

            foreach ($images as $image) {
                $path = $image->store('posts', 'public');
                $post->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()->back();
    }

    /**
     * コメント作成
     */
    public function comment(Request $request, $postId)
    {
        $request->validate([
            'comment' => 'required|string|max:500',
        ]);

        $post = Post::findOrFail($postId);

        $post->comments()->create([
            'content' => $request->input('comment'),
        ]);

        return redirect()->back();
    }

    /**
     * ✅ 非同期いいね機能
     */
    public function like(Request $request, $postId)
    {
        $post = Post::findOrFail($postId);

        // セッションから「いいね済み投稿」を取得
        $likedPosts = session()->get('liked_posts', []);

        if (in_array($postId, $likedPosts)) {
            // すでにいいねしていた → いいね取り消し
            $post->decrement('likes');

            // セッションから削除
            $likedPosts = array_diff($likedPosts, [$postId]);
            session(['liked_posts' => $likedPosts]);

            return response()->json([
                'liked' => false,
                'like_count' => $post->likes,
            ]);
        } else {
            // まだいいねしていない → 新しくいいね
            $post->increment('likes');

            $likedPosts[] = $postId;
            session(['liked_posts' => $likedPosts]);

            return response()->json([
                'liked' => true,
                'like_count' => $post->likes,
            ]);
        }
    }

    public function createPost()
    {
        // 投稿フォームを表示
        return view('posts.create-post');
    }
}
