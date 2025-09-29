<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>メッセージ一覧</title>
  <link rel="stylesheet" href="{{ asset('css\dm-list.css') }}">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="phone-frame">
  <div class="header">
    <span class="title">nexsphere</span>
  </div>
  <div id="chat-box">
  <!-- JavaScriptでスレッド一覧を挿入 -->
</div>
<div class="footer-nav">
      <a href="#" class="tab active" data-target="home"><i class="fa-solid fa-house"></i></a>
      <a href="#" class="tab" data-target="talk"><i class="fa-solid fa-comment"></i></a>
      <a href="#" class="tab" data-target="mypage"><i class="fa-solid fa-user"></i></a>
    </div>

</div>  

  <script src="{{ asset('js/dm-list.js') }}"></script>
</body>
</html>
