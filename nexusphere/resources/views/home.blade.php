<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
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

    <button id="menuBtn" class= "hamburger">
        <i class="fa-solid fa-bars"></i>
    </button>

    {{-- 投稿一覧（閲覧専用） --}}
    @foreach($posts as $post)
        <x-post_mainUnit :post="$post" :deletePost="false" />
    @endforeach

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

    <script src="{{ asset('js/home.js') }}"></script>

    <x-like-users-modal />
</body>
</html>
