<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>投稿画面</title>
  <link rel="stylesheet" href="{{ asset('css/post.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="phone-frame">
    <header class="chat-header">
      <a href="javascript:history.back()" class="back-button">🔎</a>
  <span class="title">nexusphere</span>
      <button class="menu-button">☰</button>
    </header>


    <div id="chat-box"> <!-- 既存のチャットボックス枠をフォームに流用 -->
      <form action="#" method="POST" enctype="multipart/form-data">
        {{-- @csrf --}}

        <!-- 投稿内容 -->
        <div style="margin-bottom: 10px;">
          <textarea name="content" id="message-input" rows="4" placeholder="いまどうしてる？"></textarea>
        </div>

        <!-- 画像アップロード -->
        <div style="margin-bottom: 10px;">
          <label for="image" class="file-label">+</label>
          <input type="file" name="image" id="image">
                <!-- 投稿ボタン -->
        <button type="submit">投稿</button>
        </div>
      </form>
    </div>
    <div class="footer-nav">
      <a href="#" class="tab active" data-target="home"><i class="fa-solid fa-house"></i></a>
      <a href="\posts\create" class="tab active" data-target="post"><i class="fas fa-paper-plane"></i></a>
      <a href="\dm\dm-list" class="tab active" data-target="talk"><i class="fa-solid fa-comment"></i></a>
      <a href="#" class="tab" data-target="mypage"><i class="fa-solid fa-user"></i></a>
      </div>
  </div>
</body>
</html>
