<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>メッセージ一覧</title>
  <link rel="stylesheet" href="{{ asset('css/dm_index.css') }}">
  <meta name="default-avatar" content="{{ asset('images/default-avatar.png') }}">
  <script>window.DEFAULT_AVATAR_URL = "{{asset('images/default-avatar.png')}}"</script>
</head>
<body>
  <div class="phone-frame">
    <h1>メッセージ一覧</h1>

    <ul id="dm-list" class="dm-list" data-chat-url-template="{{url('/dm')}}?to=__ID__">
    </ul>
    
  </div>

  <script src="{{ asset('js/dm.js') }}"></script>
</body>
</html>
