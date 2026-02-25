document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('#chat-box form');
  const imageInput = document.getElementById('image');
  const previewContainer = document.getElementById('preview-container');

  let selectedFiles = [];

  // 画像選択
  imageInput.addEventListener('change', () => {
    const files = Array.from(imageInput.files);
    selectedFiles = selectedFiles.concat(files);
    imageInput.value = ''; // 次回選択用にクリア
    updatePreviews();
  });

  // プレビュー更新
  function updatePreviews() {
    previewContainer.innerHTML = '';
    selectedFiles.forEach((file, index) => {
      const reader = new FileReader();
      reader.onload = (e) => {
        const imgWrapper = document.createElement('div');
        imgWrapper.style.position = 'relative';
        imgWrapper.style.display = 'inline-block';
        imgWrapper.style.margin = '5px';

        const img = document.createElement('img');
        img.src = e.target.result;
        img.style.maxWidth = '60px';
        img.style.maxHeight = '60px';
        img.style.borderRadius = '10px';
        imgWrapper.appendChild(img);

        const removeBtn = document.createElement('button');
        removeBtn.textContent = '×';
        removeBtn.style.position = 'absolute';
        removeBtn.style.top = '0px';
        removeBtn.style.right = '0px';
        removeBtn.style.background = 'rgba(0,0,0,0.5)';
        removeBtn.style.color = 'white';
        removeBtn.style.border = 'none';
        removeBtn.style.borderRadius = '50%';
        removeBtn.style.width = '20px';
        removeBtn.style.height = '20px';
        removeBtn.style.cursor = 'pointer';
        removeBtn.addEventListener('click', () => {
          selectedFiles.splice(index, 1);
          updatePreviews();
        });

        imgWrapper.appendChild(removeBtn);
        previewContainer.appendChild(imgWrapper);
      };
      reader.readAsDataURL(file);
    });
  }

  // フォーム送信
  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    // selectedFiles 配列を images[] として追加（imageInput をクリアしている実装に対応）
    selectedFiles.forEach(f => formData.append('images[]', f));

    const csrfInput = document.querySelector('input[name="_token"]');
    const csrfToken = csrfInput ? csrfInput.value : null;

    fetch(form.action || '/post', { method: 'POST', body: formData , headers: csrfToken ? {'X-CSRF-TOKEN': csrfToken} : {} ,credentials: 'same-origin' })
      .then(res => {
        if(!res.ok) throw new Error('Http' + res.status);
        alert('投稿しました');
        window.location.reload();
      })
      .catch(err => {
        console.error('投稿エラー', err);
        alert('投稿に失敗しました');
      });
  });
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