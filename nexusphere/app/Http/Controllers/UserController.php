<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use App\Models\Group;
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
        $validated = $request->validated();

        $iconPath = null;
        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('', 'direct');
        }

        $user = User::where('mail', $validated['mail'])->first();

        if ($user) {
            if ($user->email_verified_at) {
                return back()->withErrors(['mail' => 'このメールアドレスは既に登録されています'])->withInput();
            }

            $user->update([
                'password' => Hash::make($validated['password']),
                'name' => $validated['name'],
                'grade' => $validated['grade'],
                'subject' => $validated['subject'],
                'major' => $validated['major'],
                'job' => $validated['job'],
                'icon' => $iconPath,
            ]);
        } else {
            $user = User::create([
                'mail' => $validated['mail'],
                'password' => Hash::make($validated['password']),
                'name' => $validated['name'],
                'grade' => $validated['grade'],
                'subject' => $validated['subject'],
                'major' => $validated['major'],
                'job' => $validated['job'],
                'icon' => $iconPath,
            ]);
        }

        Mail::to($user->mail)->send(new VerificationEmail($user));

        return redirect()->route('login')->with('success', '登録が完了しました。認証メールが学校用のメールに届くので確認してください');
    }

    public function verifyEmail(int $user_id, string $hash)
    {
        $user = User::findOrFail($user_id);

        if (sha1($user->mail) === $hash) {
            $user->forceFill(['email_verified_at' => now()])->save();
            return redirect()->route('login')->with('success', 'メールアドレスの確認が完了しました。');
        }

        return redirect()->route('login')->with('error', '無効なリンクです。');
    }

    public function search(Request $request)
    {
        $meId = Auth::id();
        abort_if(!$meId, 401);

        $keyword = trim((string)($request->query('q') ?? $request->query('keyword') ?? ''));

        if ($keyword === '') {
            return response()->json([]);
        }

        $users = User::where('user_id', '!=', $meId)
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('job', 'like', "%{$keyword}%")
                    ->orWhere('grade', 'like', "%{$keyword}%")
                    ->orWhere('subject', 'like', "%{$keyword}%")
                    ->orWhere('major', 'like', "%{$keyword}%");
            })
            ->select('user_id', 'name', 'icon', 'grade', 'subject', 'major', 'job')
            ->limit(20)
            ->get();

        return response()->json($users->map(fn($u) => [
            'user_id' => $u->user_id,
            'name' => $u->name,
            'icon' => $u->icon ? asset('storage/icons/' . $u->icon) : null,
            'subject' => $u->subject,
            'major' => $u->major,
            'grade' => $u->grade,
            'job' => $u->job,
        ])->values());
    }

    public function group()
    {
        $meId = Auth::id();
        $users = User::where('user_id', '!=', $meId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($u) => [
                'id' => $u->user_id,
                'name' => $u->name,
            ]);

        return response()->json($users->values());
    }

    public function groupAssign(Request $request)
    {
        $meId = Auth::id();
        $groupId = $request->integer('group_id');

        if (!$groupId) {
            return $this->group();
        }

        $group = Group::findOrFail($groupId);
        $memberIds = $group->members()->pluck('users.user_id')->toArray();

        $users = User::where('user_id', '!=', $meId)
            ->when(!empty($memberIds), fn($q) => $q->whereNotIn('user_id', $memberIds))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($u) => [
                'id' => $u->user_id,
                'name' => $u->name,
            ]);

        return response()->json($users->values());
    }
}