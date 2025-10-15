<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
     public function newLoginForm()
     {
     return view('newlogin'); // ← return を使って Blade ファイルを表示！
     }
     public function register(Request $request){
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
}
?>