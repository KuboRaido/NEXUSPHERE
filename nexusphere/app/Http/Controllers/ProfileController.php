<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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

        $user = User::with('prcs')
                    ->where('user_id', $userId)
                    ->firstOrFail();

        return view('profile', compact(
            'profileUser',
            'isMine',
            'posts',
            'user'
        ));
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
            'name'    => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'major'   => 'nullable|string|max:255',
            'icon'    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user->name    = $request->name;
        $user->subject = $request->subject;
        $user->major   = $request->major;

        if ($request->hasFile('icon')) {

            if ($user->icon && file_exists(public_path('icons/'.$user->icon))) {
                @unlink(public_path('icons/'.$user->icon));
            }

            $dir = public_path('icons');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $filename = Str::random(40).'.'.$request->file('icon')->getClientOriginalExtension();
            $request->file('icon')->move($dir, $filename);

            $user->icon = $filename;
        }

        $user->save();

        return redirect()
            ->route('profile')
            ->with('status', 'プロフィールを更新しました。');
    }

    public function profileOther(Request $request)
    {
        $userId = Auth::id();
        $profileUser = User::findOrFail($request->user_id);

        $isMine = ($userId && ((int)$userId === (int)$profileUser->user_id));

        $posts = Prc::where('user_id', $profileUser->user_id)
                    ->whereNull('circle_id')
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('profile', compact(
            'profileUser',
            'isMine',
            'posts'
        ));
    }
}
