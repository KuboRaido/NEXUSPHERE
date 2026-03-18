<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nexusphere</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="{{ asset('css/dm.css') }}">
    <meta name="default-avatar" content="{{ asset('images/default-avatar.png') }}">
    <script>window.DEFAULT_AVATAR_URL = "{{asset('images/default-avatar.png')}}"</script>
    <script>window.storageBaseUrl = "{{ asset('storage/icons/') }}/"; </script>
</head>
<body>
    <!-- ヘッダー -->
        <header class="site-header">
            <div class="header-inner">
                <h1 id="site-title">Nexusphere</h1>
            </div>
        </header>
        <div class="chat-container"> 
        <div class="chat-header">
            <!-- 左 -->
            <a href="{{url('dmlist')}}" class="back-button">←</a>

            <!-- 中央 -->
            <span class="username">{{ $partnerName }}</span>

            <!-- 右 -->
                <div class="chat-header-right">
                    <button id="menu-btn" class="menu-btn">☰</button>

                    <div id="menu-dropdown" class="menu-dropdown hidden">
                        <button id="add-user-btn">ユーザーを追加</button>
                        <button id="leave-chat-btn" class="danger">退会する</button>
                    </div>
                </div>
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

                <textarea id="message-input" placeholder="メッセージを入力" autocomplete="off" enterkeyhint="send" ></textarea>

                <button type="submit" class="send-btn">送信</button>
            </div>
        </form>
    </div>
 <script src="{{ asset('js/dm.js') }}"></script>
</body>
</html>
