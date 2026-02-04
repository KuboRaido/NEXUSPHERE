<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ホーム</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <h1 id="site-title">Nexusphere</h1>
    </div>
</header>

<div class="search-area">
    <form method="GET" action="/home">
        <input type="text" name="search" placeholder="キーワードで検索"
                value="{{ request('search') }}" class="search-input">
        <button type="submit" class="search-btn">検索</button>
    </form>
</div>


<main class="container">

    {{-- 投稿一覧（閲覧専用） --}}
    @foreach($posts as $post)
        <div class="post" data-post-id="{{ $post->prc_id }}">

            {{-- 投稿者名 --}}
            <div class="post-header">
                <a href="{{ route('profile.other', $post->user->user_id) }}" class="user-link">
                    <img src="{{ asset('storage/icons/' . $post->user->icon )}}"
                        class="user-icon"
                        alt="icon"></img>
                    <span class="username">{{ $post->user->name }}</span>
                </a>
            </div>

            {{-- 投稿内容 --}}
            <div class="post-content">{{ $post->sentence }}</div>

            {{-- メディア (画像 or 動画) --}}
            @if ($post->images && $post->images->count() > 0)
                <div class="post-images">
                    @foreach ($post->images as $media)

                        @php
                            // 画像と動画のどちらを使うか判定
                            $filePath = $media->image ?? $media->video;

                            // null回避

                            // 拡張子取得
                            $exetension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                        @endphp

                        {{-- 画像ならimgタグ --}}
                        @if (in_array($exetension, ['jpg', 'jpeg', 'png', 'webp', 'gif']))
                            <img src="{{ asset('storage/post/' . $filePath) }}"
                                alt="投稿画像"
                                class="post-image"
                                onclick="openModal(this.src)">
                        @endif

                        {{-- 動画ならvideoタグ --}}
                        @if (in_array($exetension, ['mp4', 'mov', 'webm']))
                            <video controls
                                class="post-video"
                                style="max-width: 100%; border-radius: 8px; margin-top: 10px;">
                                <source src="{{ asset('storage/post/' . $filePath) }}" type="video/{{ $exetension }}">
                                お使いのブラウザは動画再生に対応していません。
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
                @foreach ($post->comments as $comment)
                    <div class="comment-list">
                        <a href="{{ route('profile.other', $comment->user->user_id) }}" class="user-link">
                            <img src="{{ asset('storage/icons/' . $comment->user->icon) }}" class="user-icon small">
                            <strong>{{ $comment->user->name }}</strong>
                        </a>
                        <span class="comment-text">{{ $comment->sentence }}</span>
                    </div>
                @endforeach
                @if($post->comments->count() > 3)
                    <button class="showMoreBtn">全てのコメントを見る</button>
                @endif

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
