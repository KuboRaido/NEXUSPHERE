<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>プロフィール</title>
  <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="phone-frame">

    <header class="header">
      <span class="title">{{ $profileUser->name }} profile</span>
    </header>

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
              <div class="excerpt">{{ Str::limit($post->content ?? '', 120) }}</div>
             </article>
           @empty
             <div class="no-posts">投稿はありません</div>
           @endforelse
          </div>
        </div>
      </section>
    </main>

    <div class="footer-nav">
      <a href="/home" class="tab active" data-target="home"><i class="fa-solid fa-house"></i></a>
      <a href="/create" class="tab active" data-target="post"><i class="fas fa-paper-plane"></i></a>
      <a href="/dmlist" class="tab active" data-target="talk"><i class="fa-solid fa-comment"></i></a>
      <a href="/profile" class="tab active" data-target="mypage"><i class="fa-solid fa-user"></i></a>
      <a href="/circle" class="tab active" data-target="circle"><i class="fa-solid fa-cube"></i></i></a>
    </div>

  </div>
  <script src="{{ asset('js/profile.js') }}"></script>
</body>
</html>
