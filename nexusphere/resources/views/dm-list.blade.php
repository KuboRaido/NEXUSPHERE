<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>メッセージ一覧</title>
  <link rel="stylesheet" href="{{ asset('css/dm-list.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <meta name="default-avatar" content="{{ asset('images/default-avatar.png') }}">
  <script>window.DEFAULT_AVATAR_URL = "{{asset('images/default-avatar.png')}}"</script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <h1 id="site-title">Nexusphere</h1>
        </div>
    </header>
    <div class=dmlist-container>
          <div class="search-container">
            <input type="text" id="search-input" placeholder="ユーザーを検索..." />
          </div>
          {{--検索結果表示--}}
          <ul id="search-results" class="search-results" style="display: none;"></ul>
          <button id="openPopupBtn" class="plus-button">
            <i class="fa-solid fa-plus"></i>
          </button>


      <ul id="dm-list" class="dm-list" data-chat-url-template="{{url('/dm')}}?to=__ID__"></ul>
    
        <div id="createDmModal" class="dm-modal hidden">
          <div class="dm-modal-content">
            <h3>グループDM作成</h3>
            <input id="group_name" type="text" placeholder="グループ名を入力" autocomplete="off" />

            <div id="modalFriendList">
              <!-- JSでここに友達一覧 -->
            </div>

            <div class="modal-buttons">
              <button id="createRoomBtn">作成</button>
              <button id="closeModalBtn">閉じる</button>
            </div>
          </div>
        </div>


    <div class="footer-nav">
      <a href="/home" class="tab {{ request()->is('home') ? 'active' : '' }}"><i class="fa-solid fa-house"></i></a>
      <a href="/post" class="tab {{ request()->is('post') ? 'active' : '' }}"><i class="fas fa-paper-plane"></i></a>
      <a href="/dmlist" class="tab {{ request()->is('dmlist') ? 'active' : '' }}"><i class="fa-solid fa-comment"></i></a>
      <a href="/profile" class="tab {{ request()->is('profile') ? 'active' : '' }}"><i class="fa-solid fa-user"></i></a>
      <a href="/circle" class="tab {{ request()->is('circle') ? 'active' : '' }}"><i class="fa-solid fa-cube"></i></a>
    </div>
  <script src="{{ asset('js/dm-list.js') }}"></script>
</body>
</html>
