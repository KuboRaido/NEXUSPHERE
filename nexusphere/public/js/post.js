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

        document.addEventListener("DOMContentLoaded", function () {
        const sidebar = document.getElementById("sidebar");
        const menuBtn = document.getElementById("menuBtn");
        const overlay = document.getElementById("overlay");

        menuBtn.addEventListener("click", function () {
            sidebar.classList.toggle("active");
            overlay.classList.toggle("active");
        });

        overlay.addEventListener("click", function () {
            sidebar.classList.remove("active");
            overlay.classList.remove("active");
        });
    });