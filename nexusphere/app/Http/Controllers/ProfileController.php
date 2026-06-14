<?php declare(strict_types=1);

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
        $posts = Prc::where('user_id', $profileUser->user_id)
            ->whereNull('circle_id')
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('profile', [
            'profileUser' => $profileUser,
            'isMine' => true,
            'posts' => $posts,
            'user' => $profileUser,
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
        $id = Auth::id();
        abort_if(!$id, 401);

        $user = User::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('icon')) {
            $path = $request->file('icon')->store('', 'direct');
            if (!$path) {
                return back()->withErrors('画像の保存に失敗しました');
            }
            $data['icon'] = $path;
        }

        $user->update($data);

        return redirect()->route('profile')->with('status', 'プロフィールを更新しました。');
    }

    public function profileOther(Request $request)
    {
        $userId = Auth::id();
        $profileUser = User::findOrFail($request->user_id);
        $isMine = $userId && ((int)$userId === (int)$profileUser->user_id);

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
        return view('custom');
    }
}