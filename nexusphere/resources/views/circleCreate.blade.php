<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8" />
    <title>クラブ作成</title>
    <link rel="stylesheet" href="{{ asset('css/circleCreate.css') }}">
</head>
<body>
        <div class="header">
         <a href="{{url('circle')}}" class="back-button">←</a>
         <span class="title">サークル作成</span>
        </div>
        <div class="form-container">

            <!-- 🖼️ 画像プレビュー部分 -->
            <div class="image-preview">
                <!-- labelでクリック可能にする -->
                <label for="image" class="image-label">
                    <img id="preview-image" src="" alt="+">
                </label>
            </div>

            <!-- 📝 フォーム -->
            <form action="{{ route('circle') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <input type="file" id="image" name="image" accept="image/*" required onchange="previewImage(event)" style="display:none;">
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
