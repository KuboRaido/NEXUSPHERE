<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/post.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Document</title>
</head>
<body>
<div class="phone-frame">

    <!-- ヘッダー -->
    <div class="chat-header">
        <button class="back-button">&larr;</button>
        <span>新規投稿</span>
        <button class="menu-button">&#8942;</button>
    </div>

    <!-- 投稿フォームエリア -->
    <div id="chat-box">
        <form action="#" method="POST" enctype="multipart/form-data" class="post-input" style="flex-direction: column;">
            {{-- @csrf --}}
            
            <!-- 投稿内容 -->
            <textarea name="content" id="content" rows="4" placeholder="いまどうしてる？" style="width: 100%;"></textarea>

            <!-- 画像アップロード -->
            <label class="file-label" for="image"><i class="fas fa-image"></i> 画像を選択</label>
            <input type="file" name="image" id="image">

            <!-- 投稿ボタン -->
            <div style="text-align: center; margin-top: 10px;">
                <button type="submit">投稿</button>
            </div>
        </form>
    </div>
    <div class="footer-nav">
      <a href="/home" class="tab active" data-target="home"><i class="fa-solid fa-house"></i></a>
      <a href="/create" class="tab active" data-target="post"><i class="fas fa-paper-plane"></i></a>
      <a href="/dmlist" class="tab active" data-target="talk"><i class="fa-solid fa-comment"></i></a>
      <a href="/profile" class="tab active" data-target="mypage"><i class="fa-solid fa-user"></i></a>
    </div>
</div>
</body>
</html>
