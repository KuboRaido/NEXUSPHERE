<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Nexesphere</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <h1>Nexesphere</h1>

    {{-- 投稿フォーム --}}
<form method="POST" action="/posts" enctype="multipart/form-data">
    @csrf
    <textarea name="content" placeholder="いまどうしてる？" required></textarea>
    <input type="file" name="images[]" multiple accept="image/*">
    <button type="submit" class="btn-submit">投稿する</button>
</form>

    {{-- 投稿一覧 --}}
@foreach ($posts as $post)
<div class="post">
    <div class="post-header">
        <span class="username">{{ $post->user_name }}</span>
    </div>

    <div class="post-content">
        <p>{{ $post->content }}</p>
    </div>

    {{-- 投稿画像 --}}
    <div class="post-images">
        @foreach ($post->images as $img)
            <img src="{{ asset('storage/' . $img->image_path) }}" alt="投稿画像" class="post-image" onclick="openModal(this.src)">
        @endforeach
    </div>

    {{-- アクション --}}
    <div class="post-actions">
        <form method="POST" action="/posts/{{ $post->id }}/like">
            @csrf
            <button type="submit">❤️ {{ $post->likes }}</button>
        </form>
    </div>

    {{-- コメント --}}
    <div class="comment-box">
        <form method="POST" action="/posts/{{ $post->id }}/comment">
            @csrf
            <input type="text" name="comment" placeholder="コメントを追加" required>
            <button type="submit">送信</button>
        </form>
        @foreach ($post->comments as $comment)
            <p>💬 {{ $comment->content }}</p>
        @endforeach
    </div>
</div>
@endforeach

{{-- モーダル表示用 --}}
<div class="modal" id="imageModal" onclick="closeModal()">
    <span class="modal-close" onclick="closeModal()">&times;</span>
    <img id="modalImage" src="">
</div>

<script>
function openModal(src) {
    document.getElementById('modalImage').src = src;
    document.getElementById('imageModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('imageModal').style.display = 'none';
}
</script>




</body>
</html>