<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8" />
    <title>クラブ作成</title>
    <link rel="stylesheet" href="{{ asset('css/circleCreate.css') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <h1 id="site-title">Nexusphere</h1>
        </div>
    </header>
        <div class="form-container">

            <!-- 🖼️ 画像プレビュー部分 -->
            <div class="image-preview">
                <!-- labelでクリック可能にする -->
                <div class="image-preview">
                    <label for="image" class="image-label">
                        <span id="plus">＋</span>
                        <img id="preview-image" style="display:none;">
                        <!-- @error('image')
                            <div class="text-danger" style="color: red;">
                                {{ $message }}
                            </div>
                        @enderror -->
                    </label>
                </div>
            </div>

            <!-- 📝 フォーム -->
            <form action="{{ route('circle') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <input type="file" id="image" name="image" accept="image/*" required onchange="previewImage(event)" style="display:none;">
                </div>

                <div class="form-group">
                    <label for="name">サークル名</label>
                    <input type="text" id="name" name="name" maxlength="30" required>
                </div>

                <div class="form-group">
                    <label for="circle_type" class="label-text">サークルタイプ</label>
                    <select id="circle_type" name="circle_type" class="input-field">
                        <option value="" selected>サークルのタイプを選択してください</option>
                        <option value="ゆるく楽しむ系">ゆるく楽しむ系</option>
                        <option value="本気でやる系">本気でやる系</option>
                        <option value="勉強・研究系">勉強・研究系</option>
                        <option value="イベント・告知系">イベント・告知系</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">サークル説明</label>
                    <textarea id="description" name="sentence" rows="5" maxlength="500" required></textarea>
                </div>
                <div class="form-group">
                    <label for="activity_frequency" class="label-text">活動頻度</label>
                    <select id="activity_frequency" name="activity_frequency" class="input-field" disabled>
                    <option value="" disabled selected>まずタイプを選択してください</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="hashtags">ハッシュタグ欄</label>
                    <input id="hashtags" type="text" name="hashtags">
                </div>
                <div class="button-row">
                    <button type="submit" class="submit-btn">作成</button>
                    <a href="{{url('circle')}}" class="back-button">戻る</a>
                </div>       
            </form>
        </div>
    <script src="{{ asset('js/circleCreate.js') }}"></script>
</body>
</html>
