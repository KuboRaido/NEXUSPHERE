<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexusphere</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body>

    <div class="container">
        {{-- サイトタイトル --}}
        <h1 id="site-title">Nexusphere</h1>
        
        {{-- 投稿フォーム --}}
        <form method="POST" action="{{ url('/posts') }}" enctype="multipart/form-data">
            @csrf
            <textarea name="content" placeholder="いまどうしてる？" required></textarea>
            <input type="file" name="images[]" multiple accept="image/*">
            <button type="submit" class="btn-submit">投稿</button>
        </form>

        {{-- 投稿一覧 --}}
        @foreach($posts as $post)
            <div class="post">
                <div class="post-header">
                    <span class="username">{{ $post->user_name }}</span>
                </div>

                <div class="post-content">{{ $post->content }}</div>

                @if ($post->images && $post->images->count() > 0)
                    <div class="post-images">
                        @foreach ($post->images as $image)
                            <img src="{{ asset('storage/' . $image->image_path) }}" alt="投稿画像" class="post-image" onclick="openModal(this.src)">
                        @endforeach
                    </div>
                @endif

                <div class="post-actions">
                    <form method="POST" action="/posts/{{ $post->id }}/like">
                        @csrf
                        <button type="submit">❤️ {{ $post->likes }}</button>
                    </form>
                </div>

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
    </div>

    {{-- モーダル --}}
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
