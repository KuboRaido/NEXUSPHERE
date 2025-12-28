<?php

namespace App\Http\Controllers;

use App\Models\Circle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CircleController extends Controller
{
    //サークルトップ画面（一覧）
    public function circleFront()
    {
        return view('circle');
    }

    //サークル一覧取得 API
    public function circleBack(Circle $circle)
    {
        $userId = Auth::id();
        $rows = Circle::with(['members' => function ($q) use ($userId) {
            $q->where('circle_users.user_id', $userId)
            ;}])
            ->orderByDesc('created_at')
            ->get();

        $list = $rows->map(function (Circle $circle) use ($userId) {

            $isOwner = $circle->owner_id === $userId;

            $isMember = $circle->members->isNotEmpty();

            $role = $isOwner ? 'owner' : ($isMember ? 'member' : 'guest');
            return [
                'circle_id'      => $circle->circle_id,
                'circle_name'    => $circle->circle_name,
                'category'       => $circle->category,
                'members_count'  => $circle->members_count,
                'sentence'       => $circle->sentence,
                'icon'           => $circle->icon ? Storage::url($circle->icon) : null,
                'role'           => $role,
            ];
        })->values();

        return response()->json($list);
    }

    //サークル作成画面
    public function circleCreateFront()
    {
        return view('circleCreate');
    }

    //サークル作成処理
    public function circleCreate(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'sentence'    => 'required|string|max:255',
            'image'       => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'category'    => 'nullable|string',
        ]);

        $iconPath = null;
        if ($request->hasFile('image')) {
            $iconPath = $request->file('image')->store('icons', 'public');
        }

        DB::transaction(function () use ($data, $iconPath) {
            $circle = Circle::create([
                'owner_id'      => Auth::id(),
                'circle_name'   => $data['name'],
                'sentence'      => $data['sentence'],
                'category'      => $data['category'],
                'icon'          => $iconPath,
                'members_count' => 0,
            ]);

            // 作成者をメンバーに追加
            $circle->members()->syncWithoutDetaching([Auth::id()]);

            // メンバー数更新
            $circle->update([
                'members_count' => $circle->members()->count(),
            ]);
        });

        return redirect()->route('circle')->with('status', 'サークルを作成しました。');
    }

    //サークル参加
    public function join(Circle $circle)
    {
        DB::transaction(function () use ($circle) {
            $circle->members()->syncWithoutDetaching([Auth::id()]);

            $circle->update([
                'members_count' => $circle->members()->count(),
            ]);
        });

        return back()->with('status', 'サークルに参加しました');
    }

    /**
     * サークル退会
     */
    public function leave(Circle $circle)
    {
        DB::transaction(function () use ($circle) {
            $circle->members()->detach(Auth::id());

            $circle->update([
                'members_count' => $circle->members()->count(),
            ]);
        });

        return back()->with('status', 'サークルを退会しました');
    }

    public function circleProfileFront(Circle $circle)
    {
        $userId = Auth::id();
        $isOwner  = $circle->owner_id === $userId;
        $isMember = $circle->members()->where('circle_users.user_id', $userId)->exists();
        $role     = $isOwner ? 'owner' : ($isMember ? 'member' : 'guest');

        return view('circleProfile', compact('circle','isMember', 'role'),);
    }

    /*サークル投稿画面*/
    public function circlePostFront()
    {
        return view('circlePost');
    }

    public function circleEdit(Circle $circle)
    {
        return view('circleprofile_edit', ['circle' => $circle]);
    }

    public function circleDmFront(Circle $circle)
    {
        $userId = Auth::id() ?? abort(401);
        $isMember = $circle->members()->where('circle_users.user_id', $userId)->exists();
        $circle_id   = $circle->circle_id;
        $groupId = $circle->group_id;
        return view('circledm',['circle_name' => $circle->circle_name,'circle_id' => $circle_id, 'userId' => $userId, 'groupId' => $groupId]);
    }
}
