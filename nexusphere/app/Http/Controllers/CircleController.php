<?php

namespace App\Http\Controllers;

use App\Models\Circle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CircleController extends Controller
{
    /**
     * サークルトップ画面（一覧）
     */
    public function circleFront()
    {
        // もし circle.blade.php 内で $joined を参照してもエラーにならないように
        return view('circle', [
            'joined' => false,
        ]);
    }

    /**
     * サークル一覧取得 API
     */
    public function circleBack()
    {
        $rows = Circle::orderByDesc('created_at')->get();

        $list = $rows->map(function (Circle $circle) {
            return [
                'circle_id'      => $circle->circle_id,
                'circle_name'    => $circle->circle_name,
                'category'       => $circle->category,
                'members_count'  => $circle->members_count,
                'sentence'       => $circle->sentence,
                'icon'           => $circle->icon ? Storage::url($circle->icon) : null,
            ];
        })->values();

        return response()->json($list);
    }

    /**
     * サークル作成画面
     */
    public function circleCreateFront()
    {
        return view('circleCreate');
    }

    /**
     * サークル作成処理
     */
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

    /**
     * サークル参加
     */
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
        $joined = $circle->members()
            ->where('user_id', Auth::id())
            ->exists();

        return view('circleProfile', [
            'circle' => $circle,
            'joined' => $joined,
        ]);
    }

    /**
     * サークル投稿画面
     */
    public function circlePostFront()
    {
        return view('circlePost');
    }

    public function circleEdit(Circle $circle)
    {
        return view('circleprofile_edit', ['circle' => $circle]);
    }
}
