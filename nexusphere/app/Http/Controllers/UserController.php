<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Jobs\SendVerificationEmail;
use App\Http\Controllers\Controller;
use App\Mail\VerificationEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function newLoginForm()
    {
        return view('newlogin');
    }

    public function register(Request $request)
    {
        $request->validate([
            'mail' => ['required','email','unique:users,mail','regex:/@(edu.sba|edu.ssm|sba|ssm)\.ac\.jp$/'],
            'password' => ['required','string','min:8','max:20','confirmed'],
            'name' => [ 'required','string','max:255'],
            'age' =>[ 'required','integer','min:0','max:120' ],
            'grade' => [ 'required','integer','min:1','max:4' ],
            'subject' =>[ 'required','string','max:255' ],
            'major' =>[ 'required','string','max:255' ],
            'icon' => [ 'nullable','image','mimes:jpeg,png,jpg,gif','max:2048' ],
        ]);

        $iconPath = null;
        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('', 'direct');
        }

        $user=User::create([
            'mail' => $request->mail,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'age' => $request->age,
            'grade' => $request->grade,
            'subject' => $request->subject,
            'major' => $request->major,
            'icon' => $iconPath,
        ]);
    
        // SendVerificationEmail::dispatch($user);
        Mail::to($user->mail)->send(new VerificationEmail($user));

        return redirect()->route('login')->with('success', '登録が完了しました！ログインしてください');
    }

    //新規登録時にメールが存在するか確認するよう
    public function verifyEmail($user_id, $hash)
    {
        $user = User::findOrFail($user_id);

        if (sha1($user->mail) === $hash) {
            // メール確認完了日時を更新（カラムが存在する場合）
            $user->forceFill(['email_verified_at' => now()])->save();
            return redirect()->route('login')->with('success', 'メールアドレスの確認が完了しました。');
        }

        return redirect()->route('login')->with('error', '無効なリンクです。');
    }

    public function search(Request $request)
    {
        // 認証ユーザーの ID を取得して未ログインなら拒否
        $meId = Auth::id();
        abort_if(!$meId, 401);

        // フロントは q パラメータで送る想定（互換のため keyword も受け取る）
        $keyword = (string) ($request->query('q') ?? $request->query('keyword') ?? '');
        $keyword = trim($keyword);

        if ($keyword === '') {
            return response()->json([]);
        }

        // 正しい LIKE 構文で部分一致検索。自分は除外する。
        $users = User::where('name', 'like', '%' . $keyword . '%')
            ->where('user_id', '!=', $meId)
            ->select('user_id', 'name', 'icon')
            ->limit(20)
            ->get();

        return response()->json($users->map(function ($u) {
            return [
                'user_id' => $u->user_id,
                'name'    => $u->name,
                'avatar'  => $u->avatar_url ?? ($u->icon ? Storage::url($u->icon) : null),
            ];
        })->values());
    }

    public function group()
    {
        $meId =Auth::id();
        $User = User::query()
                        ->where('user_id', '!=', $meId)
                        ->orderBy('created_at','desc')
                        ->get()
                        ->map(fn ($u) =>
            [
                'id' => $u->user_id,
                'name'    => $u->name,
                //'icon'  => $u->avatar_url,
            ]);

        return response()->json($User->values());
    }
}