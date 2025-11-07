<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規投稿 - Nexusphere</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body>

    {{-- サイトタイトル --}}
    <header class="site-header">
        <div class="header-inner">
            <h1 id="site-title">Nexusphere</h1>
        </div>
    </header>

    <main class="container">
        {{-- 投稿フォーム --}}
        <form method="POST" action="{{ route('post.back') }}" enctype="multipart/form-data">
            @csrf
            <textarea name="sentence" placeholder="いまどうしてる？" required></textarea>
             <div id="preview-container"></div>
            <input type="file" name="images[]" multiple accept="image/*">
            <button type="submit" class="btn-submit">投稿</button>
        </form>

    <div class="footer-nav">
      <a href="/home" class="tab active" data-target="home"><i class="fa-solid fa-house"></i></a>
      <a href="/post" class="tab active" data-target="post"><i class="fas fa-paper-plane"></i></a>
      <a href="/dmlist" class="tab active" data-target="talk"><i class="fa-solid fa-comment"></i></a>
      <a href="/profile" class="tab active" data-target="mypage"><i class="fa-solid fa-user"></i></a>
      <a href="/circle" class="tab active" data-target="circle"><i class="fa-solid fa-cube"></i></a>
    </div>
  </div>
    </main>

</body>
</html>
