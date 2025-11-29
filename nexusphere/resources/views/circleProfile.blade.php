<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>プロフィール</title>
  <link rel="stylesheet" href="{{ asset('css/circlepf.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header class="header">
  <span class="title">profile</span>
</header>

<main class="container">
  <section class="profile-card">

    <div class="profile-body">

      <!-- アイコン -->
      <div class="avatar-wrap">
        <img class="avatar" src="" alt="アイコン">
      </div>

      <div class="main">

        <!-- 名前ゾーン -->
        <div class="name-row">

          <div class="name-set">
            <div class="circle-name">サークル名: Sample Circle</div>
            <div class="circle-desc">サークル説明がここに入ります。最大2〜3行まで想定しています。</div>
          </div>

          <!-- アクションボタン（役割ごとに表示） -->
          <div class="actions">
            <!-- オーナー専用 -->
            <a class="btn role-owner" href="">編集</a>
            <a class="btn role-owner role-member" href="">DM</a>
            <a class="btn role-owner role-member" href="">投稿</a>

            <!-- 一般ユーザー専用 -->
            <a class="btn role-general" href="">参加する</a>
          </div>

        </div>
      </div>
    </div>

    <!-- 投稿一覧 -->
    <div class="content">
      <div class="left">
        <h4>最近の投稿</h4>
      </div>
    </div>

  </section>
</main>

<!-- フッターナビ -->
<div class="footer-nav">
  <a href="/home"><i class="fa-solid fa-house"></i></a>
  <a href="/post"><i class="fas fa-paper-plane"></i></a>
  <a href="/dmlist"><i class="fa-solid fa-comment"></i></a>
  <a href="/profile"><i class="fa-solid fa-user"></i></a>
  <a href="/circle"><i class="fa-solid fa-cube"></i></a>
</div>
<!-- JS読み込み -->
<script src="{{ asset('js/circleprofile.js') }}"></script>
</body>
</html>
