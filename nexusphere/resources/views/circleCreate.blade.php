<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8" />
    <title>クラブ作成</title>
    <link rel="stylesheet" href="{{ asset('css/circleCreate.css') }}">
</head>
<body>
    <div class="phone-frame">
        <div class="header">
            <span class="title">サークル作成</span>
        </div>
        <div class="form-container">
            <h1>クラブ作成</h1>
            <!-- 画像プレビュー -->
            <div class="image-preview">
                <img id="preview-image" src="" alt="画像を選択すると表示されます">
            </div>

            <!-- フォーム開始 -->
            <form action="{{ route('circle') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="image">クラブ画像（必須）</label>
                    <input type="file" id="image" name="image" accept="image/*" required onchange="previewImage(event)">
                </div>

                <div class="form-group">
                    <label for="name">クラブ名（必須）</label>
                    <input type="text" id="name" name="name" maxlength="30" required>
                </div>

                <div class="form-group">
                    <label for="category">カテゴリ（必須）</label>
                    <input type="text" id="category" name="category" maxlength="30" required>
                </div>

                <div class="form-group">
                    <label for="description">説明（必須）</label>
                    <textarea id="description" name="sentence" rows="5" maxlength="500" required></textarea>
                </div>

                <button type="submit" class="submit-btn">作成する</button>
            </form>
    </div>

    <script src="{{ asset('js/circleCreate.js') }}"></script>
</body>
</html>
