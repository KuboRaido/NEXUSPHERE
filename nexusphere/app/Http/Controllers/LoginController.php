<?php
namespace App\Http\Controllers;

use App\Http\controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
    return view('login'); // ← return を使って Blade ファイルを表示！
    }
    public function login(Request $request)
    {
        $request->validate([
            'mail'=>'required|email',
            'password'=>'required'
        ]);


        $user = User::where('mail',$request->mail)->first();

        if(!$user|| !Hash::check($request->password,$user->password)){
            return back()->withErrors([
        'login_error' => 'メールアドレスまたはパスワードが間違っています',
            ]);}

        return response()->json(['message'=>'ログイン成功'],200);

    }
}



?>