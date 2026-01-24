<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規投稿 - Nexusphere</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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
        <form method="POST" action="{{ route('post.back') }}" enctype="multipart/form-data">
            @csrf
            <textarea name="sentence" placeholder="いまどうしてる？" required></textarea>
            <div id="preview-container"></div>

            {{-- ファイル選択（標準の見た目を隠して自作ボタンを表示） --}}
            <div style="display:flex; gap:10px; margin: 10px 0;">
                <label style="cursor:pointer; padding:10px 16px; background:#f0f2f5; border-radius:20px; font-size:0.9rem; color:#555; display:inline-flex; align-items:center; gap:6px; transition:0.2s;">
                    <i class="fa-regular fa-image"></i> 画像を追加
                    <input type="file" name="images[]" multiple accept="image/*" style="display:none;">
                </label>

                <label style="cursor:pointer; padding:10px 16px; background:#f0f2f5; border-radius:20px; font-size:0.9rem; color:#555; display:inline-flex; align-items:center; gap:6px; transition:0.2s;">
                    <i class="fa-solid fa-video"></i> 動画を追加
                    <input type="file" name="videos[]" multiple accept="video/*" style="display:none;">
                </label>
            </div>

            <p class="file-note">※画像 or 30秒以内の動画を選択できます</p>
            <button type="submit" class="btn-submit">投稿</button>
        </form>
    </main>

    <div class="footer-nav">
        <a href="/home" class="tab {{ request()->is('home') ? 'active' : '' }}"><i class="fa-solid fa-house"></i></a>
        <a href="/post" class="tab {{ request()->is('post') ? 'active' : '' }}"><i class="fas fa-paper-plane"></i></a>
        <a href="/dmlist" class="tab {{ request()->is('dmlist') ? 'active' : '' }}"><i class="fa-solid fa-comment"></i></a>
        <a href="/profile" class="tab {{ request()->is('profile') ? 'active' : '' }}"><i class="fa-solid fa-user"></i></a>
        <a href="/circle" class="tab {{ request()->is('circle') ? 'active' : '' }}"><i class="fa-solid fa-cube"></i></a>
    </div>

    {{-- プレビュー表示用スクリプト --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.querySelector('input[name="images[]"]');
            const videoInput = document.querySelector('input[name="videos[]"]');
            const previewContainer = document.getElementById('preview-container');

            // プレビューエリアのスタイル調整
            previewContainer.style.display = 'flex';
            previewContainer.style.flexWrap = 'wrap';
            previewContainer.style.gap = '10px';
            previewContainer.style.marginTop = '10px';

            function updatePreview() {
                previewContainer.innerHTML = ''; // 一旦クリア

                // 画像処理
                if (imageInput.files && imageInput.files.length > 0) {
                    Array.from(imageInput.files).forEach(file => {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.style.width = '100px';
                            img.style.height = '100px';
                            img.style.objectFit = 'cover';
                            img.style.borderRadius = '8px';
                            previewContainer.appendChild(img);
                        }
                        reader.readAsDataURL(file);
                    });
                }

                // 動画処理
                if (videoInput.files && videoInput.files.length > 0) {
                    Array.from(videoInput.files).forEach(file => {
                        const video = document.createElement('video');
                        video.src = URL.createObjectURL(file);
                        video.style.height = '120px'; // 高さを固定
                        video.style.borderRadius = '8px';
                        video.controls = true; // 再生コントロールを表示
                        previewContainer.appendChild(video);
                    });
                }
            }

            // 画像選択時のイベント
            imageInput.addEventListener('change', function() {
                updatePreview();
                checkFileSize();
            });
            // 動画選択時のイベント
            videoInput.addEventListener('change', function() {
                updatePreview();
                checkFileSize();
            });

            // ファイルサイズチェック機能
            function checkFileSize() {
                const MAX_SIZE_MB = 500; // サーバー側の設定に合わせる(500MB)
                const MAX_SIZE_BYTES = MAX_SIZE_MB * 1024 * 1024;
                const submitButton = document.querySelector('.btn-submit');
                
                // エラーメッセージ表示用の要素を取得または作成
                let errorContainer = document.getElementById('file-size-error');
                if (!errorContainer) {
                    errorContainer = document.createElement('div');
                    errorContainer.id = 'file-size-error';
                    errorContainer.style.color = '#e74c3c'; // 赤色
                    errorContainer.style.fontSize = '0.9rem';
                    errorContainer.style.marginTop = '10px';
                    errorContainer.style.fontWeight = 'bold';
                    // 送信ボタンの前に挿入
                    submitButton.parentNode.insertBefore(errorContainer, submitButton);
                }

                let totalSize = 0;
                
                if (imageInput.files) {
                    Array.from(imageInput.files).forEach(file => totalSize += file.size);
                }
                if (videoInput.files) {
                    Array.from(videoInput.files).forEach(file => totalSize += file.size);
                }

                if (totalSize > MAX_SIZE_BYTES) {
                    const currentSizeMB = (totalSize / (1024 * 1024)).toFixed(1);
                    errorContainer.textContent = `合計ファイルサイズが上限(${MAX_SIZE_MB}MB)を超えています。現在のサイズ: ${currentSizeMB}MB`;
                    submitButton.disabled = true;
                    submitButton.style.opacity = '0.5';
                    submitButton.style.cursor = 'not-allowed';
                } else {
                    errorContainer.textContent = '';
                    submitButton.disabled = false;
                    submitButton.style.opacity = '1';
                    submitButton.style.cursor = 'pointer';
                }
            }
        });
    </script>
</body>
</html>
