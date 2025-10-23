<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>プロフィール編集</title>
  <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
</head>
<body>
  <div class="container">
    <h1>プロフィール編集</h1>

    @if ($errors->any())
      <div class="alert alert-danger">
        <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
      @csrf

      <div class="field">
        <label>現在のアイコン</label><br>
        <img src="{{ $user->avatar_url }}" alt="" style="width:80px;height:80px;border-radius:50%;">
      </div>

      <div class="field">
        <label for="icon">アイコン（任意）</label>
        <input id="icon" type="file" name="icon" accept="image/*">
      </div>

      <div class="field">
        <label for="name">名前</label>
        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required>
      </div>

      <div class="actions">
        <button type="submit" class="btn">保存</button>
        <a href="{{ route('profile') }}" class="btn secondary">戻る</a>
      </div>
    </form>
  </div>
</body>
</html>