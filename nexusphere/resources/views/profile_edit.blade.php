<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>プロフィール編集</title>

  <link rel="stylesheet" href="{{ asset('css/profile_edit.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <script>
    window.DEFAULT_AVATAR_URL = "{{ asset('images/default-avatar.png') }}";
  </script>
</head>
<body>

<header class="header">
  <span class="title">profile編集</span>
</header>

<div class="container">

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul>
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- プロフィール更新 --}}
  <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="field">
      <label>現在のアイコン</label>
      <div class="current-avatar">
        <img
          src="{{ $user->icon ? asset('icons/'.$user->icon) : asset('images/default-avatar.png') }}"
          alt="アイコン"
          class="avatar-preview"
        >
      </div>
    </div>

    <div class="field">
      <label for="icon">アイコン変更</label>
      <input id="icon" type="file" name="icon" accept="image/*">
    </div>

    <div class="field">
      <label for="name">名前</label>
      <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}">
    </div>

    <div class="field">
      <label for="subject">学科</label>
      <input id="subject" type="text" name="subject" value="{{ old('subject', $user->subject) }}">
    </div>

    <div class="field">
      <label for="major">専攻</label>
      <input id="major" type="text" name="major" value="{{ old('major', $user->major) }}">
    </div>

    <div class="actions">
      <button type="submit" class="btn">保存</button>
      <a href="{{ route('profile') }}" class="btn secondary">戻る</a>
    </div>
  </form>

</div>
</body>
</html>
