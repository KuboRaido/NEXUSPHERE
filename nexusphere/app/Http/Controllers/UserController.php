use Illuminate\Support\Str;

public function register(Request $request)
{
    $request->validate([
        'mail' => ['required','email','unique:users,mail','regex:/@(edu.sba|edu.ssm|sba|ssm)\.ac\.jp$/'],
        'password' => ['required','string','min:8','max:20','confirmed'],
        'name' => ['required','string','max:255'],
        'age' => ['required','integer','min:0','max:120'],
        'grade' => ['required','integer','min:1','max:4'],
        'subject' => ['required','string','max:255'],
        'major' => ['required','string','max:255'],
        'icon' => ['nullable','image','mimes:jpeg,png,jpg,gif','max:2048'],
    ]);

    $iconName = null;

    if ($request->hasFile('icon')) {
        // public/icons が無ければ作る（保険）
        $dir = public_path('icons');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $iconName = Str::random(40) . '.' . $request->file('icon')->getClientOriginalExtension();
        $request->file('icon')->move($dir, $iconName);
    }

    $user = User::create([
        'mail' => $request->mail,
        'password' => Hash::make($request->password),
        'name' => $request->name,
        'age' => $request->age,
        'grade' => $request->grade,
        'subject' => $request->subject,
        'major' => $request->major,
        'icon' => $iconName, // ← ファイル名だけ保存
    ]);

    SendVerificationEmail::dispatch($user);

    return redirect()->route('login')->with('success', '登録が完了しました！ログインしてください');
}
