<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>プロフィール編集</title>
  <link rel="stylesheet" href="{{ asset('css/profile_edit.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <meta name="default-avatar" content="{{ asset('images/default-avatar.png') }}">
  <script>window.DEFAULT_AVATAR_URL = "{{asset('images/default-avatar.png')}}"</script>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
</head>
<body>
   <header class="header">
      <span class="title">profile編集</span>
    </header>
  <div class="container">

      @if ($errors->any())
        <div class="alert alert-danger">
          <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      <form method="POST" action="{{ route('profile-update') }}" enctype="multipart/form-data">
        @csrf

        <div class="field">
          <label>現在のアイコン</label>
          <div class="current-avatar">
            <img src="{{ $user->avatar_url }}" alt="アイコン" class="avatar-preview">
          </div>
        </div>

      <div class="field">
        <label for="icon">アイコン</label>
        <input id="icon" type="file" name="icon" accept="image/*">
      </div>

      <div class="field">
        <label for="name">名前</label>
        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}">
      </div>

      <div class="filed">
        <label for="job" class="label-text">区分</label>
        <select id="job" name="job" class="input-filed" required>
          <option value="" disabled selected>区分を選択してください</option>
          <option value="学生">学生</option>
          <option value="講師">講師</option>
          <option value="教員">教員</option>
        </select>
      </div>

      <div class="filed">
        <label for="grade">学年</label>
        <select id="grade" name="grade" class="input-field" disabled>
          <option value="" disabled selected>学年を選してください</option>
          <option value="1">1</option>
          <option value="2">2</option>
          <option value="3">3</option>
          <option value="4">4</option>
        </select>
      </div>

      <div class="field">
        <label for="subject" class="label-text">学科</label>
        <select id="subject" name="subject" class="input-field" disabled>
            <option value="" disabled selected>学科を選択してください</option>
            <option value="AI&テクノロジー科">AI&テクノロジー科</option>
            <option value="デジタルテクノロジー科">デジタルテクノロジー科</option>
            <option value="クリエイティブデザイン科">クリエイティブデザイン科</option>
        </select>
      </div>

      <div class="field">
            <label for="major" class="label-text">専攻</label>
            <select id="major" name="major" class="input-field" disabled>
                <option value="" disabled selected>先に学科を選択してください</option>
            </select>
      </div>

        <div class="actions">
          <button type="submit" class="btn">保存</button>
          <a href="{{ route('profile') }}" class="btn secondary">戻る</a>
        </div>
      </form>
    </div>
    <script src="{{ asset('js/profile_edit.js') }}"></script>
</body>
</html>
