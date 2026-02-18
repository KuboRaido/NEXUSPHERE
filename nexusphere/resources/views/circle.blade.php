<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>サークル一覧</title>
  <link rel="stylesheet" href="{{ asset('css/circle.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <meta name="default-avatar" content="{{ asset('images/default-club.png') }}">
  <script>window.DEFAULT_CLUB_ICON_URL = "{{ asset('images/default-club.png') }}";</script>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <!-- <script>
    // JSから参照できるベースURLを定義
    window.storageBaseUrl = "{{ asset('storage/icons/') }}";
  </script> -->
</head>
  <body>
    <!-- ヘッダー -->
    <header class="site-header">
        <div class="header-inner">
            <h1 id="site-title">Nexusphere</h1>
        </div>
    </header>
      <div class=circle-container>
      <!-- 検索ボックス -->
      <div class="search-container">
        <input type="text" id="search-input" placeholder="サークルを検索..." />
        <a href="circle/create" class="attach-btn">＋</a>
      </div>

      <!-- 検索結果 -->
      <ul id="search-results" class="search-results" style="display: none;"></ul>

      <!-- ▼ タブ切り替え -->
      <div class="circle-tabs">
        <button class="tab-btn active" data-target="joined">参加中</button>
        <button class="tab-btn" data-target="not-joined">未参加</button>
      </div>

      <!-- ▼ 参加中サークル -->
      <ul id="circle-joined" class="circle-section"></ul>

      <!-- ▼ 未参加サークル -->
      <ul id="circle-not-joined" class="circle-section" style="display:none;"></ul>


      <!-- サークル一覧 -->
      <ul id="circle-list" class="circle-list" data-club-url-template="{{ url('/circle/__ID__') }}?"></ul>
    </div>
    <div class="footer-nav">
        <a href="/home" class="tab {{ request()->is('home') ? 'active' : '' }}"><i class="fa-solid fa-house"></i><span>ホーム</span></a>
        <a href="/post" class="tab {{ request()->is('post') ? 'active' : '' }}"><i class="fas fa-paper-plane"></i><span>投稿</span></a>
        <a href="/dmlist" class="tab {{ request()->is('dmlist') ? 'active' : '' }}"><i class="fa-solid fa-comment"></i><span>DM</span></a>
        <a href="/profile" class="tab {{ request()->is('profile') ? 'active' : '' }}"><i class="fa-solid fa-user"></i><span>プロフィール</span></a>
        <a href="/circle" class="tab {{ request()->is('circle') ? 'active' : '' }}"><i class="fa-solid fa-cube"></i><span>サークル</span></a>
    </div>

    <script src="{{ asset('js/circle-list.js') }}"></script>
  </body>
</html>
