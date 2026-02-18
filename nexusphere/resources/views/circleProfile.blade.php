<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>プロフィール</title>
  <link rel="stylesheet" href="{{ asset('css/circlepf.css') }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script> window.USER_ROLE = "{{ $role }}";</script>
</head>
<body>

<header class="header">
  <span class="title">{{ $circle->circle_name }}</span>
</header>

<main class="container">
  <section class="profile-card">

    <div class="profile-body">

      <!-- アイコン -->
      <div class="avatar-wrap">
          <img
            class="avatar"
            src="{{ $circle->icon
                    ? asset('storage/icons/' . $circle->icon)
                    : asset('images/default-avatar.png') }}"
            alt="アイコン"
          >

      </div>


      <div class="main">

        <!-- 名前ゾーン -->
        <div class="name-row">

          <div class="name-set">
            <div class="circle-name">{{ $circle->circle_name }}</div>
            <div class="circle-desc">{{$circle->sentence}}</div>
          </div>

          <!-- アクションボタン（役割ごとに表示） -->
          <div class="actions">
            <!-- オーナー専用 -->
            <a class="btn role-owner" href="{{ route('circle.edit',['circle' => $circle->circle_id]) }}">編集</a>
            <a class="btn role-owner" href="{{ route('circle.request',['circle' => $circle->circle_id]) }}">申請</a>
            <a class="btn role-owner role-member" href="{{ route('circle.dm',['circle' => $circle->circle_id]) }}">DM</a>
            <a class="btn role-owner role-member" href="{{ route('circle.post',['circle' => $circle->circle_id]) }}">投稿</a>
            <a class="btn role-owner role-member" href="{{ route('circle.cancel',['circle' => $circle->circle_id]) }}">退会</a>
            <!-- 一般ユーザー専用 -->
              <form class = "btn role-guest" method="POST" action="{{ route('circle.join', ['circle' => $circle->circle_id ])}}" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').textContent = '送信中...';">
                @csrf
                <button type="submit">参加申請</button>
              </form>
          </div>

        </div>
      </div>
    </div>

    <!-- 投稿一覧 -->
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
                                <img src="{{ asset('storage/post/' . $media->image) }}" alt="画像" style="max-width: 200px; border-radius: 4px; object-fit: cover;">
                            @elseif($media->video)
                                <video src="{{ asset('storage/post/' . $media->video) }}" controls style="max-width: 200px; border-radius: 4px;"></video>
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

<!-- フッターナビ -->
<div class="footer-nav">
  <a href="/home" class="tab {{ request()->is('home') ? 'active' : '' }}"><i class="fa-solid fa-house"></i><span>ホーム</span></a>
  <a href="/post" class="tab {{ request()->is('post') ? 'active' : '' }}"><i class="fas fa-paper-plane"></i><span>投稿</span></a>
  <a href="/dmlist" class="tab {{ request()->is('dmlist') ? 'active' : '' }}"><i class="fa-solid fa-comment"></i><span>DM</span></a>
  <a href="/profile" class="tab {{ request()->is('profile') ? 'active' : '' }}"><i class="fa-solid fa-user"></i><span>プロフィール</span></a>
  <a href="/circle" class="tab {{ request()->is('circle') ? 'active' : '' }}"><i class="fa-solid fa-cube"></i><span>サークル</span></a>
 </div>
<!-- JS読み込み -->
<script src="{{ asset('js/circleprofile.js') }}"></script>
</body>
</html>
