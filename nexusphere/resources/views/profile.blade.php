<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>プロフィール</title>
  <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <h1 id="site-title">Nexusphere</h1>
        </div>

      <button id="menuBtn" class="hamburger">
        <i class="fa-solid fa-bars"></i>
      </button>

<!--ここからログアウトボタン-->
      @if($isMine)
        <button type="button" class="logout-btn" id="logout-trigger">ログアウト</button>
      @endif
    </header>
      @if($isMine)
        <div id="logout-confirm" class="logout-confirm" hidden>
          <div class="logout-dialog">
            <p>ログアウトしますか？</p>
            <div class="logout-actions">
                <button type="button" id="logout-yes">はい</button>
                <button type="button" id="logout-no">いいえ</button>
            </div>
          </div>
        </div>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" hidden>
          @csrf
        </form>
      @endif
<!--ここまで-->

    <main class="container">
      <section class="profile-card" aria-label="ユーザープロフィール">

        <div class="profile-body">
          <div class="avatar-wrap">
            <img
              class="avatar"
              src="{{ $profileUser->icon
                  ? asset('storage/icons/' . $profileUser->icon)
                  : asset('images/default-avatar.png') }}"
              alt="アイコン"
            >
          </div>
          <div class="main">
            <div class="name-row">
              <div>
                <div class="display-name">{{ $profileUser->name }}</div>
                <div class="handle3">{{ $profileUser->job}}</div>
                <div class="handle4">{{ $profileUser->grade}}</div>
                <div class="handle">{{ $profileUser->subject }}</div>
                <div class="handle2">{{ $profileUser->major }}</div>
              </div>

              <div class="actions">
                  @if($isMine)
                    <a class="btn" href="{{ route('profile.edit') }}">編集</a>
                  @endif
                    <a class="dm-btn" href="{{ url('/dm') }}?to={{ $isMine ? 'me' : $profileUser->user_id }}">DM</a>
              </div>
            </div>
            <!-- <div class="portfolio-section">
              <button class="portfolio-toggle"><i class="fa-solid fa-chevron-down"></i>
            </button>
            <div class="portfolio-content">
                <ul></ul>
              </div> -->
            </div>
          </div>
        </div>


        <div class="content">
          <div class="left">
            <h4>最近の投稿</h4>
              @foreach($posts as $post)
                  @if($isMine)
                    <x-post_mainUnit :post="$post" :user_id="$userId" :deletePost="true"/>
                  @else
                    <x-post_mainUnit :post="$post" :deletePost="false"/>
                  @endif
              @endforeach
          </div>
        </div>
      </section>
    </main>

    <div id="sidebar" class="footer-nav">
        <a href="/home" class="tab {{ request()->is('home') ? 'active' : '' }}"><i class="fa-solid fa-house"></i><span>ホーム</span></a>
        <a href="/post" class="tab {{ request()->is('post') ? 'active' : '' }}"><i class="fas fa-paper-plane"></i><span>投稿</span></a>
        <a href="/dmlist" class="tab {{ request()->is('dmlist') ? 'active' : '' }}"><i class="fa-solid fa-comment"></i><span>DM</span></a>
        <a href="/profile" class="tab {{ request()->is('profile') ? 'active' : '' }}"><i class="fa-solid fa-user"></i><span>プロフィール</span></a>
        <a href="/circle" class="tab {{ request()->is('circle') ? 'active' : '' }}"><i class="fa-solid fa-cube"></i><span>サークル</span></a>
    </div>
  <script src="{{ asset('js/module/post_mainUnit.js') }}"></script>

    <div id="overlay" class="overlay"></div>
  <script src="{{ asset('js/profile.js') }}"></script>
    <x-like-users-modal />
</body>
</html>
