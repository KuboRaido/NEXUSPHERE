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
    <div class="header">
      <span class="title">DM</span>
    </div>
    
    <div class="search-container">
      <input type="text" id="search-input" placeholder="ユーザーを検索..." />
    </div>
    {{--検索結果表示--}}
    <ul id="search-results" class="search-results" style="display: none;"></ul>

     <ul id="dm-list" class="dm-list" data-chat-url-template="{{url('/dm')}}?to=__ID__"></ul>
    
    <div class="footer-nav">
      <a href="/home" class="tab active" data-target="home"><i class="fa-solid fa-house"></i></a>
      <a href="/post" class="tab active" data-target="post"><i class="fas fa-paper-plane"></i></a>
      <a href="/dmlist" class="tab active" data-target="talk"><i class="fa-solid fa-comment"></i></a>
      <a href="/profile" class="tab active" data-target="mypage"><i class="fa-solid fa-user"></i></a>
      <a href="/circle" class="tab active" data-target="circle"><i class="fa-solid fa-cube"></i></i></a>
    </div>
  <script src="{{ asset('js/dm-list.js') }}"></script>
</body>
</html>
