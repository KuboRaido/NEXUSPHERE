<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>プロフィール</title>
  <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
</head>
<body>
    <header class="header">
      <span class="title">{{ $profileUser->name }} profile</span>
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
            <img class="avatar" src="{{ $profileUser->avatar_url }}" alt="アイコン">
          </div>

          <div class="main">
            <div class="name-row">
              <div>
                <div class="display-name">{{ $profileUser->name }}</div>
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
            <div class="portfolio-section">
              <button class="portfolio-toggle"><i class="fa-solid fa-chevron-down"></i>
            </button>
            <div class="portfolio-content">
                <ul></ul>
              </div>
            </div>
          </div>
        </div>


        <div class="content">
          <div class="left">
            <h4>最近の投稿</h4>

              @forelse($posts as $post)
                  <article class="post">
                    <div class="title">{{ $post->sentence ?? '(タイトルなし)' }}</div>
                    
                      {{-- 画像・動画がある場合ループして表示 --}}
                      @if($post->images->isNotEmpty())
                          <div class="post-media" style="margin: 10px 0; display: flex; flex-wrap: wrap; gap: 10px;">
                              @foreach($post->images as $media)
                                  @if($media->image)
                                      <img src="{{ Storage::url($media->image) }}" alt="画像" style="max-width: 200px; border-radius: 4px; object-fit: cover;">
                                  @elseif($media->video)
                                      <video src="{{ Storage::url($media->video) }}" controls style="max-width: 200px; border-radius: 4px;"></video>
                                  @endif
                              @endforeach
                          </div>
                      @endif
                    </article>
                  @empty
                    <div class="no-posts">投稿はありません</div>
                @endforelse
          </div>
        </div>
      </section>
    </main>

    <div class="footer-nav">
      <a href="/home" class="tab {{ request()->is('home') ? 'active' : '' }}"><i class="fa-solid fa-house"></i></a>
      <a href="/post" class="tab {{ request()->is('post') ? 'active' : '' }}"><i class="fas fa-paper-plane"></i></a>
      <a href="/dmlist" class="tab {{ request()->is('dmlist') ? 'active' : '' }}"><i class="fa-solid fa-comment"></i></a>
      <a href="/profile" class="tab {{ request()->is('profile') ? 'active' : '' }}"><i class="fa-solid fa-user"></i></a>
      <a href="/circle" class="tab {{ request()->is('circle') ? 'active' : '' }}"><i class="fa-solid fa-cube"></i></a>
    </div>
  <script src="{{ asset('js/profile.js') }}"></script>
</body>
</html>
