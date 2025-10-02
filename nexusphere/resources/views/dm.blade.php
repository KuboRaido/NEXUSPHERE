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
    <div class="header">
      <span class="title">nexusphere</span>
    </div>
    
<!--チャットボックス-->
    <div id="chat-box" class="chat-box">
     <a href="{{url('dmlist')}}" id="back-button" class="back-button">←</a> 
    </div>
     <input type="hidden" id="currentUserId" value="{{auth()->id()}}">
     <input type="hidden" id="recipientId" value="{{$partnerId}}">
<!--入力欄-->
    <form id="chat-form" class="chat-form" autocomplete="off">
       <div class="input-area">
         <input type="file" id="image-input" accept="image/*,video/*" style="display:none;" onchange="previewImage(event)" multiple hidden>
         <button type="button" id="attach-btn"class="attach-btn" onclick="document.getElementById('image-input').click()">+</button>
         @csrf
         <input id="message-input" type="text" placeholder="" autocomplete="off" enterkeyhint="send" inputmode="text"/>
         <button onclick="sendMessage()">送信</button>
       </div>
     </form>
  </div>
  
  <script src="{{ asset('js/dm.js') }}" ></script>

</body>
