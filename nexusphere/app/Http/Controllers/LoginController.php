<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
    return view('login'); // ← return を使って Blade ファイルを表示！
    }
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'mail'=> ['required','email'],
            'password'=>['required'],
        ]);

        $remember = (bool) $request->boolean('remember');

        if (Auth::attempt(['mail' => $credentials['mail'],'password' =>$credentials['password']],$remember)){
            
            // メール確認済みかチェック
            if (Auth::user()->email_verified_at === null) {
                Auth::logout(); // ログイン状態を解除
                return back()->withErrors([
                    'login_error' => 'メールアドレスの確認が完了していません。確認メールのリンクをクリックしてください。'
                ])->onlyInput('mail');
            }

            /** @var \App\Models\User $user */
            $user = Auth::user();

            // 強制参加させたいサークルのID
            $officialCircle = 7;
            if ($user) {
                $user->circles()->syncWithoutDetaching([$officialCircle]);
            }

            $request->session()->regenerate();

            if($request->wantsJson()){
                return response()->json(['ok' => true, 'id' => Auth::id()]);
            }
            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'login_error' => 'メールアドレスまたはパスワードが間違っています',
        ])->onlyInput('mail');

    }

    public function logout(Request $request)
    {
        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}



?>