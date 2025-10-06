<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        $posts = Post::where('user_id', $user->id)
                     ->orderBy('created_at', 'desc')
                     ->get();

        return view('profile', compact('user', 'posts'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('profile_edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'department' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user->department = $request->input('department');
        $user->major = $request->input('major');

        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('icons', 'public');
            $user->icon = $iconPath;
        }

        $user->save();

        return redirect()->route('profile.show')->with('success', 'プロフィールを更新しました。');
    }
}
