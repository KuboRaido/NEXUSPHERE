<?php declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'mail' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = (bool)$request->boolean('remember');

        if (!Auth::attempt(['mail' => $credentials['mail'], 'password' => $credentials['password']], $remember)) {
            return back()->withErrors([
                'login_error' => 'メールアドレスまたはパスワードが間違っています',
            ])->onlyInput('mail');
        }

        $user = Auth::user();

        if ($user->email_verified_at === null) {
            Auth::logout();
            return back()->withErrors([
                'login_error' => 'メールアドレスの確認が完了していません。確認メールのリンクをクリックしてください。'
            ])->onlyInput('mail');
        }

        $user->circles()->syncWithoutDetaching([7]);

        $request->session()->regenerate();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'id' => Auth::id()]);
        }

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}