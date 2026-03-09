<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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
        <div class="register-link">
        <p>アカウントをお持ちでない方は <a href="{{ route('newLogin') }}">新規登録はこちら</a></p>
      </div>
    </div>
</body>
</html>
