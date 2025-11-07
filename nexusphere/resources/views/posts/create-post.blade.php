<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規投稿 - Nexusphere</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body>

    {{-- サイトタイトル --}}
    <header class="site-header">
        <div class="header-inner">
            <h1 id="site-title">Nexusphere</h1>
        </div>
    </header>

    <main class="container">
        {{-- 投稿フォーム --}}
        <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
            @csrf
            <textarea name="content" placeholder="いまどうしてる？" required></textarea>
            <input type="file" name="images[]" multiple accept="image/*">
            <button type="submit" class="btn-submit">投稿</button>
        </form>
    </main>

</body>
</html>
