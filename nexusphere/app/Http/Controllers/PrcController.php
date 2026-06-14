<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Prc;
use App\Http\Requests\StorePrcRequest;
use App\Http\Resources\PostResource;
use App\Services\PostService;
use App\Services\LikeService;
use App\Services\CommentService;

class PrcController extends Controller
{
    public function __construct(
        private PostService $postService,
        private LikeService $likeService,
        private CommentService $commentService
    ) {
    }
    public function index(Request $request)
    {
        $circleId = $request->input('circle_id');

        if (empty($circleId)) {
            $query = Prc::where('type', 0)
                ->whereNull('circle_id')
                ->with(['user', 'comments', 'images'])
                ->orderBy('created_at', 'desc');

            if ($request->filled('search')) {
                $keyword = $request->input('search');
                $query->where('sentence', 'LIKE', "%{$keyword}%");
            }

            $posts = $query->get();

            return view('home', compact('posts'));
        }

        return view('home', ['posts' => []]);
    }

    public function store(StorePrcRequest $request)
    {
        $validated = $request->validated();
        $post = $this->postService->createPost(
            Auth::user(),
            $validated['sentence'],
            $request->file('images', []),
            $request->file('videos', [])
        );

        return redirect()->back();
    }

    public function delete(int $postId)
    {
        $post = Prc::findOrFail($postId);

        if ($post->user_id === Auth::id()) {
            $post->delete();
        } else {
            abort(403, 'Unauthorized');
        }

        return back();
    }

    public function circleStore(StorePrcRequest $request)
    {
        $circleId = (int)$request->input('circle_id');

        $isMember = \App\Models\Circle_user::where('circle_id', $circleId)
            ->where('user_id', Auth::id())
            ->exists();
        abort_if(!$isMember, 403, 'サークルに参加していません');

        $validated = $request->validated();
        $post = $this->postService->createPost(
            Auth::user(),
            $validated['sentence'],
            $request->file('images', []),
            $request->file('videos', []),
            $circleId
        );

        return redirect()->route('circle.profile', ['circle' => $circleId]);
    }
    public function comment(Request $request, int $postId)
    {
        $request->validate([
            'comment' => ['required', 'string', 'max:500']
        ]);

        $post = Prc::findOrFail($postId);
        $comment = $this->commentService->create($post, Auth::user(), $request->input('comment'));

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('components.comment_item', ['comment' => $comment])->render(),
            ]);
        }

        return back();
    }

    public function like(Request $request, int $postId)
    {
        $post = Prc::findOrFail($postId);
        $result = $this->likeService->toggle($post, Auth::user());

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return back();
    }

    public function likedUser(int $postId)
    {
        $post = Prc::findOrFail($postId);
        $likedUsers = $this->likeService->getLikedUsers($post);

        return response()->json($likedUsers);
    }

    // 投稿フォーム表示
    public function post()
    {
        $userId = Auth::id();
        abort_if(!$userId, 401);

        $post = Auth::user();
        return view('post',['post' => $post]);
    }
}
