<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>メッセージ一覧</title>
  <link rel="stylesheet" href="{{ asset('css/dm-list.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <meta name="default-avatar" content="{{ asset('images/default-avatar.png') }}">
  <script>window.DEFAULT_AVATAR_URL = "{{asset('images/default-avatar.png')}}"</script>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <div class="phone-frame">
    <div class="header">
      <span class="title">DM</span>
    </div>
    
     <ul id="dm-list" class="dm-list" data-chat-url-template="{{url('/dm')}}?to=__ID__"></ul>
    
    <div class="footer-nav">
      <a href="#" class="tab active" data-target="home"><i class="fa-solid fa-house"></i></a>
      <a href="/create" class="tab active" data-target="post"><i class="fas fa-paper-plane"></i></a>
      <a href="/dmlist" class="tab active" data-target="talk"><i class="fa-solid fa-comment"></i></a>
      <a href="/profile" class="tab active" data-target="mypage"><i class="fa-solid fa-user"></i></a>
    </div>

  </div>
  <script src="{{ asset('js/dm-list.js') }}"></script>
</body>
</html>
