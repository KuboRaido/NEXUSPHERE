<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // バリデーション
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'age' => 'nullable|integer',
            'grade' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:50',
            'major' => 'nullable|string|max:50',
            'icon' => 'nullable|image|max:2048',
        ]);

        // アイコン保存
        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('icons', 'public');
        } else {
            $iconPath = null;
        }

        // ユーザー作成
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'age' => $validated['age'] ?? null,
            'grade' => $validated['grade'] ?? null,
            'department' => $validated['department'] ?? null,
            'major' => $validated['major'] ?? null,
            'icon' => $iconPath,
        ]);

        // ログイン後リダイレクト
        auth()->login($user);
        return redirect('/feed');
    }
}
