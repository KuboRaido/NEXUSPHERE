<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Prc;
use App\Models\User;

class ProfileController extends Controller
{
    public function profileFront()
    {
        $userId = Auth::id();
        abort_if(!$userId, 401);

        $profileUser = Auth::user();
        $isMine = true;

        $posts = Prc::where('user_id', $profileUser->user_id)
                        ->whereNull('circle_id')
                        ->orderBy('created_at', 'desc')
                        ->get();
                
        $user = User::with('prcs')->where('user_id',$userId)->firstOrFail();

        return view('profile', [
            'profileUser' => $profileUser,
            'isMine' => $isMine,
            'posts' => $posts,
            'user' => $user,
        ]);
    }

    public function edit()
    {
        $user = Auth::user();
        abort_if(!$user, 401);
        return view('profile_edit', compact('user'));
    }

    public function update(Request $request)
    {
        $id = Auth::id();
        abort_if(!$id, 401);

        $user = User::where('user_id', $id)->firstOrFail();

        $request->validate([
            'name'       => 'required|string|max:255',
            'subject'    => 'nullable|string|max:255',
            'job'        => 'required|string|max:2',
            'grade'      => 'nullable|string|max:2',
            'major'      => 'nullable|string|max:255',
            'icon'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ],[],[
            'name'       => '名前',
            'subject'    => '学部',
            'job'        => '区分',
            'grade'      => '学年',
            'major'      => '学科',
            'icon'       => 'アイコン',
        ]);

    $user->name     = $request->input('name');
    $user->subject  = $request->input('subject');
    $user->job      = $request->input('job');
    $user->grade    = $request->input('grade');
    $user->major    = $request->input('major');
    // ファイル入力は input() では取得しない。アップロードがあった場合のみ上書きする。

        if ($request->hasFile('icon')) {
            $path = $request->file('icon')->store('', 'direct');
            $user->icon = $path; // 例: icons/2025/10/31/xxxx.png （public ディスク）
        }

        $user->save();

        return redirect()->route('profile')->with('status', 'プロフィールを更新しました。');
    }

    public function profileOther(Request $request){
        $userId = Auth::id();
        $profileUser = User::findOrFail($request->user_id);
        $isMine = ($userId && ((int)$userId === $profileUser));

        $posts = Prc::where('user_id', $profileUser->user_id)
                    ->whereNull('circle_id')
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('profile', [
            'profileUser' => $profileUser,
            'isMine' => $isMine,
            'posts' => $posts,
        ]);
    }
}