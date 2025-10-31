<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DM</title>
    <link rel="stylesheet" href="{{ asset('css/dm.css') }}">
    <meta name="default-avatar" content="{{ asset('images/default-avatar.png') }}">
    <script>window.DEFAULT_AVATAR_URL = "{{asset('images/default-avatar.png')}}"</script>
</head>
<body>
  <div class="phone-frame">

    <!-- ヘッダー -->
    <div class="header">
      <span class="title">nexusphere</span>
    </div>

  <div class="chat-header">
      <a href="{{url('dmlist')}}" class="back-button">←</a>
      <span class="username">{{ $partnerName }}</span>
  </div>

    <!-- チャットボックス -->
    <div id="chat-box" class="chat-box"></div>
    <!-- 入力フォーム（これが唯一のフォーム） -->
    <form id="chat-form" class="chat-form" autocomplete="off">
      <input type="hidden" id="currentUserId" value="{{auth()->id()}}">
      <input type="hidden" id="recipientId" value="{{ $partnerId }}">

      <div id="preview-area" class="preview-area"></div>

      <div class="input-area">
        <button type="button" id="attach-btn" class="attach-btn">＋</button>
        <input type="file" id="image-input" accept="image/*,video/*" style="display:none;" multiple>

        <input id="message-input" type="text" placeholder="メッセージを入力" autocomplete="off" enterkeyhint="send" />

        <button type="submit" class="send-btn">送信</button>
      </div>
    </form>

  </div>
  
  <script src="{{ asset('js/dm.js') }}"></script>
</body>
</html>
