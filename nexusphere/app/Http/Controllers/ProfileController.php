<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pcr;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // show logged-in user's profile
    public function profileFront()
    {
        $userId = Auth::id();
        abort_if(!$userId, 401);

        $profileUser = Auth::user();
        $isMine = true;

        $posts = Pcr::where('user_id', $profileUser->id)
                     ->orderBy('created_at', 'desc')
                     ->get();

        return view('profile', [
            'profileUser' => $profileUser,
            'isMine' => $isMine,
            'posts' => $posts,
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
            'major'      => 'nullable|string|max:255',
            'icon'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ],[],[
            'name'       => '名前',
            'subject'    => '学部',
            'major'      => '学科',
            'icon'       => 'アイコン',
        ]);

        $user->name = $request->input('name');
        $user->subject = $request->input('subject');
        $user->major = $request->input('major');
        $user->icon = $request->input('icon');

        if ($request->hasFile('icon')) {
            $path = $request->file('icon')->store('icons', 'public');
            $user->icon = $path;
        }

        $user->save();

        return redirect()->route('profile')->with('status', 'プロフィールを更新しました。');
    }

    public function profileOther(User $user){
        $userId = Auth::id();
        $isMine = ($userId && ((int)$userId === (int)$user->user_id));

        $posts = Pcr::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('profile', [
            'profileUser' => $user,
            'isMine' => $isMine,
            'posts' => $posts,
        ]);
    }
}
