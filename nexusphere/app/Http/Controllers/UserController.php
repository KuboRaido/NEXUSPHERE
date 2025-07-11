<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    public function register(Request $request){
        $request->validate([
            'mail' =>'required|string|unique:user',
            'password' =>'required|sting|min:8|max:20|confirmed',
            'name' =>'required|text|max:255',
            'age' =>'required|integer|max:2',
            'grade' =>'required|integer|min:1',
            'subject' =>'required|text|max:',
            'major' =>'required|text|max:255',
            'icon' =>'required|text',
        ]);

        User::create([
            'mail' => $request->mail,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'age' => $request->age,
            'grade' => $request->grade,
            'subject' => $request->subject,
            'major' => $request->subject,
            'icon' => $request,
        ]);

        return response()->json(['message' => '登録完了!'], 201);
    }
}
?>