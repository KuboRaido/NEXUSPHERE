<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <!-- 外部CSSを読み込む -->
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="login-container">
        <h1>ログイン</h1>
         @if (session('success'))
           <div class="alert alert-success">
             {{ session('success') }}
           </div>
         @endif

    <form method="POST" action="{{ route('login') }}">
            @csrf
            <input type="email" name="mail" placeholder="Username or email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
            @if ($errors->has('login_error'))
             <div style="color: red; margin-bottom: 10px;">
               {{ $errors->first('login_error') }}
             </div>
           @endif
        </form>
    </div>
</body>
</html>
