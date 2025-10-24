<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function newLoginForm()
    {
        return view('newlogin');
    }

    public function register(Request $request)
    {
        $request->validate([
            'mail' =>'required|string|unique:users',
            'password' =>'required|string|min:8|max:20|confirmed',
            'name' =>'required|string|max:255',
            'age' =>'required|integer|min:0|max:120',
            'grade' =>'required|integer|min:1|max:4',
            'subject' =>'required|string|max:255',
            'major' =>'required|string|max:255',
            'icon' =>'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $iconPath = null;
        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('icons', 'public');
        }

        User::create([
            'mail' => $request->mail,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'age' => $request->age,
            'grade' => $request->grade,
            'subject' => $request->subject,
            'major' => $request->major,
            'icon' => $iconPath,
        ]);

        return redirect()->route('login')->with('success', '登録が完了しました！ログインしてください');
    }

    public function search(Request $request)
    {
        // 認証ユーザーの ID を取得して未ログインなら拒否
        $meId = Auth::id();
        abort_if(!$meId, 401);

        // フロントは q パラメータで送る想定（互換のため keyword も受け取る）
        $keyword = (string) ($request->query('q') ?? $request->query('keyword') ?? '');
        $keyword = trim($keyword);

        if ($keyword === '') {
            return response()->json([]);
        }

        // 正しい LIKE 構文で部分一致検索。自分は除外する。
        $users = User::where('name', 'like', '%' . $keyword . '%')
            ->where('user_id', '!=', $meId)
            ->select('user_id', 'name', 'icon')
            ->limit(20)
            ->get();

        return response()->json($users->map(function ($u) {
            return [
                'user_id' => $u->user_id,
                'name'    => $u->name,
                // avatar_url アクセサがあれば優先して返す
                'avatar'  => $u->avatar_url ?? ($u->icon ? Storage::url($u->icon) : null),
            ];
        })->values());
    }
}
