<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Circle;
use App\Models\Prc;
use App\Models\Circle_requests;
use App\Rules\NgWord;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCircleRequest;
use Illuminate\Support\Facades\Auth;
use App\Services\CircleService;

class CircleController extends Controller
{
    public function __construct(private CircleService $circleService)
    {
    }

    public function circleFront()
    {
        return view('circle');
    }

    public function circleBack()
    {
        $userId = Auth::id();
        $rows = Circle::with(['members' => fn($q) => $q->where('circle_users.user_id', $userId)])
            ->withCount('members')
            ->orderByDesc('created_at')
            ->get();

        $list = $rows->map(fn(Circle $circle) => [
            'circle_id' => $circle->circle_id,
            'circle_name' => $circle->circle_name,
            'category' => $circle->category,
            'members_count' => $circle->members_count,
            'sentence' => $circle->sentence,
            'icon' => $circle->icon ? asset('storage/icons/' . $circle->icon) : null,
            'role' => $circle->owner_id === $userId ? 'owner' : ($circle->members->isNotEmpty() ? 'member' : 'guest'),
        ])->values();

        return response()->json($list);
    }

    public function circleCreateFront()
    {
        return view('circleCreate');
    }

    public function circleCreate(StoreCircleRequest $request)
    {
        $iconPath = null;
        if ($request->hasFile('image')) {
            $iconPath = $request->file('image')->store('', 'direct');
        }

        $this->circleService->createCircle(Auth::user(), $request->validated(), $iconPath);

        return redirect()->route('circle')->with('status', 'サークルを作成しました。');
    }

    public function circleMember(Circle $circle)
    {
        $members = $circle->members()
            ->select('users.user_id', 'users.name', 'users.grade', 'users.job')
            ->orderByDesc('circle_users.created_at')
            ->get();

        return view('circlemer', ['members' => $members]);
    }

    public function circleRequest(Circle $circle)
    {
        $requests = $circle->joinRequests()
            ->with(['user:user_id,name,icon', 'circle:circle_id'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn(Circle_requests $req) => [
                'circle_request_id' => $req->circle_request_id,
                'circle_id' => $req->circle_id,
                'user_id' => $req->user_id,
                'user_name' => $req->user?->name,
                'status' => $req->status,
            ]);

        return view('circleRequest', [
            'circle' => $circle,
            'requests' => $requests,
        ]);
    }

    public function join(Circle $circle, Request $request)
    {
        try {
            $this->circleService->requestJoin($circle, $request->user());
            return back()->with('status', '参加申請を送信しました');
        } catch (\Exception $e) {
            return back()->withErrors('参加申請が送信できませんでした');
        }
    }

    public function approve(Circle $circle, Circle_requests $circleRequest)
    {
        $this->circleService->approveRequest($circleRequest, Auth::user());
        return back()->with('status', '参加を承認しました');
    }

    public function reject(Circle $circle, Circle_requests $circleRequest)
    {
        $this->circleService->rejectRequest($circleRequest, Auth::user());
        return back()->with('status', '参加申請を拒否しました');
    }

    public function circleCancel(Circle $circle)
    {
        $this->circleService->leaveCircle($circle, Auth::user());
        return redirect()->route('circle')->with('status', 'サークルを退会しました');
    }

    public function update(Circle $circle, Request $request)
    {
        abort_if(!Auth::id(), 401);

        $request->validate([
            'circle_name' => ['nullable', 'string', 'max:255', new NgWord],
            'sentence' => ['nullable', 'string', 'max:255', new NgWord],
            'icon' => ['nullable', 'image', 'max:5120'],
        ]);

        $iconPath = null;
        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('', 'direct');
        }

        $this->circleService->updateCircle($circle, Auth::user(), $request->all(), $iconPath);

        return redirect()
            ->route('circle.profile', ['circle' => $circle->circle_id])
            ->with('status', 'プロフィールを更新しました。');
    }

    public function circleProfileFront(Circle $circle)
    {
        $userId = Auth::id();
        $isOwner = $circle->owner_id === $userId;
        $isMember = $circle->members()->where('circle_users.user_id', $userId)->exists();

        $isPending = Circle_requests::where('circle_id', $circle->circle_id)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->exists();

        $posts = Prc::where('circle_id', $circle->circle_id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('circleProfile', [
            'user' => $userId,
            'circle' => $circle,
            'isMember' => $isMember,
            'isPending' => $isPending,
            'role' => $isOwner ? 'owner' : ($isMember ? 'member' : 'guest'),
            'posts' => $posts
        ]);
    }

    public function circlePostFront(Circle $circle)
    {
        return view('circlePost', [
            'circle' => $circle->circle_id,
            'userId' => Auth::id(),
        ]);
    }

    public function circleEdit(Circle $circle)
    {
        return view('circleprofile_edit', ['circle' => $circle]);
    }

    public function circleDmFront(Circle $circle)
    {
        $userId = Auth::id() ?? abort(401);

        return view('circledm', [
            'circle_name' => $circle->circle_name,
            'circle_id' => $circle->circle_id,
            'userId' => $userId,
            'groupId' => $circle->group_id,
        ]);
    }
}
