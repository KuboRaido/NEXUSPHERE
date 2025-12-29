<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>プロフィール</title>
  <link rel="stylesheet" href="{{ asset('css/circlepf.css') }}">
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
            src="{{ $circle->icon? Storage::url($circle->icon): asset('images/default-circle.png') }}"
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
            <a class="btn role-owner role-member" href="{{ route('circle.dm',['circle' => $circle->circle_id]) }}">DM</a>
            <a class="btn role-owner role-member" href="{{ route('circle.post',['circle' => $circle->circle_id]) }}">投稿</a>
            <!-- 一般ユーザー専用 -->
            <a class="btn role-guest" href="">参加する</a>
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
              <div class="excerpt">{{ Str::limit($post->sentence ?? '', 120) }}</div>
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
  <a href="/home" class="tab {{ request()->is('home') ? 'active' : '' }}"><i class="fa-solid fa-house"></i></a>
  <a href="/post" class="tab {{ request()->is('post') ? 'active' : '' }}"><i class="fas fa-paper-plane"></i></a>
  <a href="/dmlist" class="tab {{ request()->is('dmlist') ? 'active' : '' }}"><i class="fa-solid fa-comment"></i></a>
  <a href="/profile" class="tab {{ request()->is('profile') ? 'active' : '' }}"><i class="fa-solid fa-user"></i></a>
  <a href="/circle" class="tab {{ request()->is('circle') ? 'active' : '' }}"><i class="fa-solid fa-cube"></i></a>
 </div>
<!-- JS読み込み -->
<script src="{{ asset('js/circleprofile.js') }}"></script>
</body>
</html>
