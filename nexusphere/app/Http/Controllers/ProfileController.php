<?php
namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
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
                        ->whereNull('parent_id')
                        ->orderBy('created_at', 'desc')
                        ->get();
                
        $user = User::with('prcs')->where('user_id',$userId)->firstOrFail();

        return view('profile', [
            'profileUser' => $profileUser,
            'isMine' => $isMine,
            'posts' => $posts,
            'user' => $user,
            'userId' => $userId,
        ]);
    }

    public function edit()
    {
        $user = Auth::user();
        abort_if(!$user, 401);
        return view('profile_edit', compact('user'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $request->validated();
        
        $id = Auth::id();
        abort_if(!$id, 401);

        $user = User::where('user_id', $id)->firstOrFail();

        $user->name     = $request->input('name');
        $user->subject  = $request->input('subject');
        $user->job      = $request->input('job');
        $user->grade    = $request->input('grade');
        $user->major    = $request->input('major');
    // ファイル入力は input() では取得しない。アップロードがあった場合のみ上書きする。

        if ($request->hasFile('icon')) {
            file_put_contents('/tmp/icon_debug.log', "File received: " . $request->file('icon')->getClientOriginalName() . "\n", FILE_APPEND);
            $path = $request->file('icon')->store('', 'direct');
            file_put_contents('/tmp/icon_debug.log', "Store result: " . ($path === false ? 'FALSE' : $path) . "\n", FILE_APPEND);
            if($path === false){
                file_put_contents('/tmp/icon_debug.log', "Store failed!\n", FILE_APPEND);
                return back()->withErrors('画像の保存に失敗しました');
            }
            $user->icon = $path;
        } else {
            file_put_contents('/tmp/icon_debug.log', "No file received\n", FILE_APPEND);
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

    public function custom()
    {
        return view("custom");
    }
}