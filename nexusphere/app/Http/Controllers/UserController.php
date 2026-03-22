<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use App\Models\Group;
use App\Http\Controllers\Controller;
use App\Mail\VerificationEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function newLoginForm()
    {
        return view('newlogin');
    }

    public function register(RegisterUserRequest $request)
    {
        $request->validated();

        $iconPath = null;
        if ($request->hasFile('icon')) {
                    $iconPath = $request->file('icon')->store('', 'direct');
                }

                $user = User::where('mail', $request->mail)->first();

                if($user){
                    if($user->email_verified_at){
                        return back()->withErrors(['mail' => 'このメールアドレスは既に登録されています'])->withInput();
                    }

                    $user->update([
                        'password' => Hash::make($request->password),
                        'name' => $request->name,
                        'grade' => $request->grade,
                        'subject' => $request->subject,
                        'major' => $request->major,
                        'icon' => $iconPath,
                        'job' => $request->job,
                    ]);
                }else{
                    $user=User::create([
                    'mail' => $request->mail,
                    'password' => Hash::make($request->password),
                    'name' => $request->name,
                    'grade' => $request->grade,
                    'subject' => $request->subject,
                    'major' => $request->major,
                    'icon' => $iconPath,
                    'job' => $request->job,
                ]);
                }
        
                // SendVerificationEmail::dispatch($user);
                Mail::to($user->mail)->send(new VerificationEmail($user));
        
                return redirect()->route('login')->with('success', '登録が完了しました。認証メールが学校用のメールに届くので確認してください');
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
        $users = User::where('user_id', '!=', $meId)
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('job', 'like', '%' . $keyword . '%')
                    ->orWhere('grade', 'like', '%' . $keyword . '%')
                    ->orWhere('subject', 'like', '%' . $keyword . '%')
                    ->orWhere('major', 'like', '%' . $keyword . '%');
            })
            ->select('user_id', 'name', 'icon', 'grade', 'subject', 'major','job')
            ->limit(20)
            ->get();

        return response()->json($users->map(function ($u) {
            return [
                'user_id' => $u->user_id,
                'name'    => $u->name,
                'icon'    => $u->icon ? asset('storage/icons/' . $u->icon) : null,
                'subject' => $u->subject,
                'major'   => $u->major,
                'grade'   => $u->grade,
                'job'     => $u->job,
            ];
        })->values());
    }

    //DM一覧のグループ作成画面の際のユーザー一覧のデータ送信に使用
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
            ]);

        return response()->json($User->values());
    }

    public function groupAssign(Request $request)
    {
        $meId = Auth::id();
        $groupId = $request->integer('group_id');
        if (!$groupId) {
            return $this->group();
        }

        $group   = Group::findOrFail($groupId);
        $memberIds = $group->members()->pluck('users.user_id')->toArray();

        $query = User::query()
                        ->where('user_id', '!=', $meId)
                        ->orderBy('created_at', 'desc');

        if (!empty($memberIds)) {
            $query->whereNotIn('user_id', $memberIds);
        }

        $User = $query->get()
                        ->map(fn ($u) =>
            [
                'id' => $u->user_id,
                'name'    => $u->name,
            ]);

        return response()->json($User->values());
    }
}