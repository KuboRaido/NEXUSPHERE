<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規投稿 - Nexusphere</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
</head>
<body>

    {{-- サイトタイトル --}}
    <header class="site-header">
        <div class="header-inner">
            <h1 id="site-title">Nexusphere</h1>
        </div>
    </header>

    <main class="container">
        <button id="menuBtn" class= "hamburger">
            <i class="fa-solid fa-bars"></i>
        </button>
    
        {{-- 投稿フォーム --}}
        <form method="POST" action="{{ route('post.back') }}" enctype="multipart/form-data" class="createPost">
            @csrf
            <textarea name="sentence" placeholder="いまどうしてる？" required></textarea>
            <div id="preview-container"></div>

            {{-- ファイル選択（標準の見た目を隠して自作ボタンを表示） --}}
            <div style="display:flex; gap:10px; margin: 10px 0;">
                <label style="cursor:pointer; padding:10px 16px; background:#f0f2f5; border-radius:20px; font-size:0.9rem; color:#555; display:inline-flex; align-items:center; gap:6px; transition:0.2s;">
                    <i class="fa-regular fa-image"></i> 画像を追加
                    <input type="file" name="images[]" multiple accept="image/*" style="display:none;">
                </label>

                <label style="cursor:pointer; padding:10px 16px; background:#f0f2f5; border-radius:20px; font-size:0.9rem; color:#555; display:inline-flex; align-items:center; gap:6px; transition:0.2s;">
                    <i class="fa-solid fa-video"></i> 動画を追加
                    <input type="file" name="videos[]" multiple accept="video/*" style="display:none;">
                </label>
            </div>

            <p class="file-note">※画像 or 30秒以内の動画を選択できます</p>
            <button type="submit" class="btn-submit">投稿</button>
        </form>
    </main>


    <div id="sidebar" class="footer-nav">
        <a href="/home" class="tab {{ request()->is('home') ? 'active' : '' }}"><i class="fa-solid fa-house"></i><span>ホーム</span></a>
        <a href="/post" class="tab {{ request()->is('post') ? 'active' : '' }}"><i class="fas fa-paper-plane"></i><span>投稿</span></a>
        <a href="/dmlist" class="tab {{ request()->is('dmlist') ? 'active' : '' }}"><i class="fa-solid fa-comment"></i><span>DM</span></a>
        <a href="/profile" class="tab {{ request()->is('profile') ? 'active' : '' }}"><i class="fa-solid fa-user"></i><span>プロフィール</span></a>
        <a href="/circle" class="tab {{ request()->is('circle') ? 'active' : '' }}"><i class="fa-solid fa-cube"></i><span>サークル</span></a>
    </div>

    <div id="overlay" class="overlay"></div>
    <script src="{{ asset('js/post.js') }}"></script>
</body>
</html>
