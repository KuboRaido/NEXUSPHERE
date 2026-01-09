<?php

namespace App\Http\Controllers;

use App\Models\Circle;
use App\Models\Prc;
use App\Models\Circle_requests;
use App\Rules\NgWord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Redirect;

class CircleController extends Controller
{
    //サークルトップ画面（一覧）
    public function circleFront()
    {
        return view('circle');
    }

    //サークル一覧取得 API
    public function circleBack()
    {
        $userId = Auth::id();
        $rows = Circle::with(['members' => function ($q) use ($userId) {
            $q->where('circle_users.user_id', $userId)
            ;}])
            ->withCount('members')
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
            'name'        => ['required','string','max:255','unique:circles,circle_name',new NgWord],
            'sentence'    => ['required','string','max:255',new NgWord],
            'image'       => ['required','image','mimes:jpeg,png,jpg,gif,webp|max:5120'],
            'category'    => ['nullable','string', new NgWord],
        ],[
            'image.required' => '画像を設定してください',
            'name.unique'    => 'そのサークル名はすでに使用されております',
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

    public function circleRequest(Circle $circle)
    {
        $request = $circle->joinRequests()
                    ->with(['user:user_id,name,icon','circle:circle_id'])

                    ->orderByDesc('created_at')
                    ->get()
                    ->map( function (Circle_requests $request) {
                        return [
                            'circle_request_id' =>  $request->circle_request_id,
                            'circle_id'         =>  $request->circle_id,
                            'user_id'           =>  $request->user_id,
                            'user_name'         =>  $request->user?->name,
                            'user_icon'         =>  $request->user?->icon ? Storage::url($request->use->icon) : null,
                            'status'            =>  $request->status,
                        ];
                    });

        return view('circleRequest', [
            'circle' => $circle,
            'requests' => $request,
        ]);
    }

    //サークル参加
    public function join(Circle $circle, Request $request)
    {
        $user = $request->user();
        if($circle->members()->where('circle_users.circle_id', $circle->circle_id)->exists()){
            return back()->with('status', '既に参加済みのサークルです');
        }

        try{
            Circle_requests::updateOrCreate(
                ['user_id' => $user->user_id, 'circle_id' => $circle->circle_id],
                ['user_id' => $user->user_id, 'circle_id' => $circle->circle_id,'status' => 'pending','request_at' => now()]
            );
        } catch (QueryException $e){
            report($e);
            return back()->withErrors('参加申請が送信できませんでした');
        }

        return back()->with('status', '参加申請を送信しました');
    }

    public function approve(Circle $circle, Circle_requests $circle_request)
    {
        $this->authorize('manage', $circle);
        $members = $circle->members()->orderBy('created_at')->get();
        DB::transaction(function () use ($circle_request) {
            $circle_request->update(['status' => 'approved']);
            $circle_request->circle->members()->syncWithoutDetaching($circle_request->user_id);
        });
        $circle->members_count = $members->count();

        return back()->with('status', '参加を承認しました');
    }

    public function reject(Circle $circle, Circle_requests $circle_request)
    {
        $this->authorize('manage', $circle);
        $circle_request->update(['status' => 'rejected']);
        return back()->with('status', '参加申請を拒否しました');
    }

    /**
     * サークル退会
     */
    public function circleCancel(Circle $circle)
    {
        $userId = Auth::id();

        DB::transaction(function () use ($circle, $userId) {
            
            //　残りのメンバー数を取得
            $members = $circle->members()->where('circle_users.user_id','!=',$userId)->orderBy('created_at')->get();

            // メンバーから削除
            $circle->members()->detach(Auth::id());

            // もしオーナーが退会したときにメンバーが一人もいなかったらサークルを削除
            if($members->isEmpty()) {
                $circle->delete();
            } else {
                // メンバーがいる場合
                if ($circle->owner_id == $userId){
                    $nextOwner = $members->first();
                    $circle->owner_id = $nextOwner->user_id;
                    $circle->members_count = $members->count();
                    $circle->save();
                }
            }
        });

        return redirect()->route('circle')->with('status', 'サークルを退会しました');
    }

    public function update(Circle $circle, Request $request)
    {
        abort_if(!Auth::id(), 401);

        $request->validate([
            'circle_name' => ['nullable','string','max:255'],
            'sentence'    => ['nullable','string','max:255'],
            'icon'        => ['nullable','image','mimes:jpeg,png,jpg,gif,webp','max:5120',new NgWord],
        ]);

        if ($request->hasFile('icon')) {
            $path = $request->file('icon')->store('icons', 'public');
            $circle->icon = $path; // ← icons/xxxx.png
        }

        if ($request->filled('circle_name')) {
            $circle->circle_name = $request->circle_name;
        }

        if ($request->filled('sentence')) {
            $circle->sentence = $request->sentence;
        }

        $circle->save();

        return redirect()
            ->route('circle.profile', ['circle' => $circle->circle_id])
            ->with('status', 'プロフィールを更新しました。');
    }


    public function circleProfileFront(Circle $circle)
    {
        $userId = Auth::id();
        $isOwner  = $circle->owner_id === $userId;
        $isMember = $circle->members()->where('circle_users.user_id', $userId)->exists();
        $role     = $isOwner ? 'owner' : ($isMember ? 'member' : 'guest');
        $posts    = Prc::where('circle_id', $circle->circle_id)
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('circleProfile', ['circle' => $circle,'isMember' => $isMember, 'role' => $role, 'posts' => $posts ],);
    }

    /*サークル投稿画面*/
    public function circlePostFront(Circle $circle)
    {
        $userId     = Auth::id();
        $circle_id  = $circle->circle_id;

        return view('circlePost', ['circle' => $circle_id, 'userId' => $userId]);
    }

    public function circleEdit(Circle $circle)
    {
        return view('circleprofile_edit', ['circle' => $circle]);
    }

    public function circleDmFront(Circle $circle)
    {
        $userId = Auth::id() ?? abort(401);
        $circle_id   = $circle->circle_id;
        $groupId = $circle->group_id;
        return view('circledm',['circle_name' => $circle->circle_name,'circle_id' => $circle_id, 'userId' => $userId, 'groupId' => $groupId]);
    }
}
