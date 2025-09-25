<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DM</title>
    <link rel="stylesheet" href="{{ asset('css/dm.css') }}">
    <meta name="default-avatar" content="{{ asset('images/default-avatar.png') }}">
    <script>window.DEFAULT_AVATAR_URL = "{{asset('images/default-avatar.png')}}}"</script>

</head>
<body>
  <div class="phone-frame">
    <h1>DMチャット画面</h1>

    <input type="hidden" id="currentUserId" value="{{auth()->id()}}">
    <input type="hidden" id="recipientId" value="{{$partnerId}}">

    <div id="chat-box" class="chat-box"></div>

    <form id="chat-form" class="chat-form" autocomplete="off">
      @csrf
       <input
        id="message-input"
        type="text"
        placeholder=""
        autocomplete="off"
        enterkeyhint="send"
        inputmode="text"
       />
       <button id="send-btn" type="submit" aria-label="送信">送信</button>
     </form>
  </div>
  

  <script src="{{ asset('js/dm.js') }}" defer></script>
</body>
