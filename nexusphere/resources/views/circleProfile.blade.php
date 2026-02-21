<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>プロフィール</title>
  <link rel="stylesheet" href="{{ asset('css/circlepf.css') }}">
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
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
            <!--メンバー-->
            <a class="btn role-owner role-member" href="{{ route('circle.member',['circle' => $circle->circle_id]) }}">メンバー</a>
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
            {{-- ホーム画面と同じレイアウトで表示 --}}
              <div class="post" data-post-id="{{ $post->prc_id }}">
                  {{-- 投稿者名 --}}
                  <div class="post-header">
                      <a href="{{ route('profile.other', $post->user->user_id) }}" class="user-link">
                          <img src="{{ $post->user->avatar_url }}"
                              class="user-icon"
                              alt="icon">
                          <span class="username">{{ $post->user->name }}</span>
                        @if ($post->created_at->gt(now()->subWeek()))
                          <time class="post_time">{{ $post->created_at->diffForHumans() }}</time>
                        @else
                          <time class="post_time">{{ $post->created_at->format('Y年m月d日') }}</time>
                        @endif
                      </a>
                  </div>

                  {{-- 投稿内容 --}}
                  <div class="post-content">{{ $post->sentence }}</div>

                  {{-- メディア (画像 or 動画) --}}
                  @if ($post->images && $post->images->count() > 0)
                      <div class="post-images">
                          @foreach ($post->images as $media)
                              @php
                                  $filePath = $media->image ?? $media->video;
                                  $exetension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                              @endphp

                              {{-- 画像 --}}
                              @if (in_array($exetension, ['jpg', 'jpeg', 'png', 'webp', 'gif']))
                                  <img src="{{ asset('storage/post/' . $filePath) }}"
                                      alt="投稿画像"
                                      class="post-image"
                                      onclick="openModal(this.src)">
                              @endif

                              {{-- 動画 --}}
                              @if (in_array($exetension, ['mp4', 'mov', 'webm']))
                                  <video controls
                                      class="post-video"
                                      style="max-width: 100%; border-radius: 8px; margin-top: 10px;">
                                      <source src="{{ asset('storage/post/' . $filePath) }}" type="video/{{ $exetension }}">
                                  </video>
                              @endif
                          @endforeach
                      </div>
                  @endif

                  <div class="post-footer">
                      {{-- いいね --}}
                      <form method="POST" action="/posts/{{ $post->prc_id }}/like">
                          @csrf
                          <button type="submit" class="like-button">
                              ❤️ <span class="like-count">{{ $post->nices->count() }}</span>
                          </button>
                      </form>

                      {{-- コメント入力 --}}
                      <form method="POST" action="/posts/{{ $post->prc_id }}/comment" class="comment-form">
                          @csrf
                          <input type="text" name="comment" placeholder="コメントを追加" required>
                          <button type="submit">送信</button>
                      </form>
                  </div>

                  {{-- コメント一覧 --}}
                  <div class="comment-list">
                      @foreach ($post->comments as $comment)
                          <div class="comment">
                                <div class="comment_head">
                                    <a href="{{ route('profile.other', $comment->user->user_id) }}" class="user-link">
                                        <img src="{{ $comment->user->avatar_url }}" class="user-icon small">
                                        <strong class="user_name">{{ $comment->user->name }}</strong>
                                    </a>
                                    @if ($comment->created_at->gt(now()->subWeek()))
                                            <time class="comment_time">{{ $comment->created_at->diffForHumans() }}</time>
                                    @else
                                            <time class="comment_time">{{ $comment->created_at->format('Y年m月d日') }}</time>
                                    @endif
                                </div>
                                <span class="comment-text">{{ $comment->sentence }}</span>
                            </div>
                      @endforeach
                  </div>
                  @if($post->comments->count() > 3)
                      <button class="showMoreBtn">全てのコメントを見る</button>
                  @endif
              </div>
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
