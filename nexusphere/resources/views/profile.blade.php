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
      <span class="title">profile</span>
    </header>

    <main class="container">
      <section class="profile-card" aria-label="ユーザープロフィール">
        <div class="cover"></div>

        <div class="profile-body">
          <div class="avatar-wrap">
            <div class="avatar" role="img" aria-label="ユーザーのアイコン"></div>
          </div>

          <div class="main">
            <div class="name-row">
              <div>
                <div class="display-name">山田 太郎</div>
                <div class="handle">@username</div>
              </div>

              <div class="meta-row" style="margin-left:12px;">
                <div class="stat"><span class="num">12</span><span class="label">投稿</span></div>
                <div class="stat"><span class="num">45</span><span class="label">フォロワー</span></div>
                <div class="stat"><span class="num">31</span><span class="label">フォロー中</span></div>
              </div>

              <div class="actions">
                <button class="btn">編集</button>
                <button class="btn secondary">フォロー</button>
                <button class="dm-btn">DM</button>
              </div>
            </div>

            <div class="bio">入力されていません</div>
          </div>
        </div>

        <div class="content">
          <div class="left">
            <h4>最近の投稿</h4>

            <article class="post">
              <div class="title">今日のランチはパスタ！</div>
              <div class="excerpt">トマトソースが最高においしかった！</div>
            </article>

          </div>
        </div>
      </section>
    </main>

    <div class="footer-nav">
      <a href="#" class="tab active" data-target="home"><i class="fa-solid fa-house"></i></a>
      <a href="/create" class="tab active" data-target="post"><i class="fas fa-paper-plane"></i></a>
      <a href="/dmlist" class="tab active" data-target="talk"><i class="fa-solid fa-comment"></i></a>
      <a href="/profile" class="tab active" data-target="mypage"><i class="fa-solid fa-user"></i></a>
    </div>

  </div>
</body>
</html>
