<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexusphere</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    {{-- サイトタイトル --}}
    <header class="site-header">
        <div class="header-inner">
            <h1 id="site-title">Nexusphere</h1>
        </div>
    </header>

    <main class="container">
        {{-- 投稿一覧（閲覧専用） --}}
        @foreach($posts as $post)
            <div class="post" data-post-id="{{ $post->id }}">
                <div class="post-header">
                    <span class="username">{{ $post->user_name }}</span>
                </div>

                <div class="post-content">{{ $post->content }}</div>

                @if ($post->images && $post->images->count() > 0)
                    <div class="post-images">
                        @foreach ($post->images as $image)
                            <img src="{{ asset('storage/' . $image->image_path) }}" alt="投稿画像" class="post-image" onclick="openModal(this.src)">
                        @endforeach
                    </div>
                @endif

                <div class="post-actions">
                    <button type="button" class="like-button" data-post-id="{{ $post->id }}">
                        ❤️ <span class="like-count">{{ $post->likes }}</span>
                    </button>
                </div>

                <div class="comment-box">
                    <form method="POST" action="/posts/{{ $post->id }}/comment">
                        @csrf
                        <input type="text" name="comment" placeholder="コメントを追加" required>
                        <button type="submit">送信</button>
                    </form>

                    @foreach ($post->comments as $comment)
                        <p>💬 {{ $comment->content }}</p>
                    @endforeach
                </div>
            </div>
        @endforeach
    </main>

    <div class="footer-nav">
      <a href="/home" class="tab {{ request()->is('home') ? 'active' : '' }}"><i class="fa-solid fa-house"></i></a>
      <a href="/post" class="tab {{ request()->is('post') ? 'active' : '' }}"><i class="fas fa-paper-plane"></i></a>
      <a href="/dmlist" class="tab {{ request()->is('dmlist') ? 'active' : '' }}"><i class="fa-solid fa-comment"></i></a>
      <a href="/profile" class="tab {{ request()->is('profile') ? 'active' : '' }}"><i class="fa-solid fa-user"></i></a>
      <a href="/circle" class="tab {{ request()->is('circle') ? 'active' : '' }}"><i class="fa-solid fa-cube"></i></a>
    </div>

    <script src="{{ asset('js/home.js') }}"></script>

</body>
</html>
