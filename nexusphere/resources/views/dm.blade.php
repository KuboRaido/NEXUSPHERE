<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DM</title>
  <link rel="stylesheet" href="{{ asset('css/dm.css') }}">
</head>
<body>
  <div class="phone-frame">
    <div class="header">
      <span class="title">nexsphere</span>
    </div>

    <!-- チャット部分 -->
   <div id="chat-box">
  <a href="{{ url('/dm/index') }}" class="back-button">←</a>
  <input type="hidden" id="currentUserId" value="1">
  <input type="hidden" id="recipientId" value="2">

  <!-- メッセージだけここに描画 -->
  <div class="messages"></div>

  <!-- 入力欄は消えない -->
  <div class="input-area">
    <input type="file" id="image-input" accept="image/*" style="display:none;" onchange="previewImage(event)">
    <button type="button" onclick="document.getElementById('image-input').click()">+</button>
    <input type="text" id="message-input" placeholder="メッセージを入力...">
    <button onclick="sendMessage()">送信</button>
  </div>
</div>


  <script src="{{ asset('js/dm.js') }}"></script>
</body>
</html>
