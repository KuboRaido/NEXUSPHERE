<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>プロフィール編集</title>
  <link rel="stylesheet" href="{{ asset('css/circleprofile_edit.css') }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <meta name="default-avatar" content="{{ asset('images/default-avatar.png') }}">
  <script>window.DEFAULT_AVATAR_URL = "{{asset('images/default-avatar.png')}}"</script>
  <meta name="viewport" content="width=device-width, initial-scale=1">
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

      <form method="POST" action="{{ route('circle-update', $circle->circle_id) }}" enctype="multipart/form-data">
        @csrf
    <div class="field">
      <label>アイコン</label>

      <div class="avatar-row">
        <!-- 現在 -->
        <div class="avatar-box">
          <p class="avatar-label">現在</p>
          <img
            src="{{ $circle->icon
                  ? asset('storage/icons/' . $circle->icon)
                  : asset('images/default-avatar.png') }}"
            class="avatar-preview"
          >
        </div>

        <div class="arrow">→</div>

        <!-- 新しい（クリックで選択） -->
        <div class="avatar-box">
          <p class="avatar-label">新しい</p>

          <label for="image" class="avatar-click">
            <img
              id="preview"
              src="{{ $circle->icon
                    ? asset('storage/icons/' . $circle->icon)
                    : asset('images/default-avatar.png') }}"
              class="avatar-preview"
            >
          </label>
        </div>
      </div>

      <!-- 実体の file input（隠す） -->
      <input id="image" type="file" name="image" accept="image/*" hidden>
    </div>



      <div class="field">
        <label for="circle_name">名前</label>
        <input id="circle_name" type="text" name="circle_name" value="{{ old('circle_name', $circle->circle_name) }}">
      </div>

     <div class="field">
        <label for="circle_type" class="label-text">サークルタイプ</label>
        <select id="circle_type" name="circle_type" class="input-field">
            <option value="" selected>サークルのタイプを選択してください</option>
            <option value="ゆるく楽しむ系">ゆるく楽しむ系</option>
            <option value="本気でやる系">本気でやる系</option>
            <option value="勉強・研究系">勉強・研究系</option>
            <option value="イベント・告知系">イベント・告知系</option>
        </select>
      </div>
      <div class="field">
        <label for="sentence">サークル説明</label>
        <input id="sentence" type="text" name="sentence" value="{{ old('sentence', $circle->sentence) }}">
      </div>
      <div class="field">
        <label for="activity_frequency" class="label-text">活動頻度</label>
        <select id="activity_frequency" name="activity_frequency" class="input-field" disabled>
          <option value="" disabled selected>まずタイプを選択してください</option>
        </select>
      </div>
      <div class="field">
        <label for="hashtags">ハッシュタグ欄</label>
        <input id="hashtags" type="text" name="hashtags">
      </div>
        <div class="actions">
          <button type="submit" class="btn">保存</button>
          <a href="{{ route('circle.profile', $circle->circle_id) }}" class="btn secondary">戻る</a>
        </div>
      </form>
    </div>
   <script src="{{ asset('js/circlepf_edit.js') }}"></script>
</body>
</html>
