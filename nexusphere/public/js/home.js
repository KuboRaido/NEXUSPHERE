// いいね非同期
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    document.querySelectorAll('.like-button').forEach(button => {
        button.addEventListener('click', async () => {

            const response = await fetch('/home', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            button.querySelector('.like-count').textContent = data.like_count;
        });
    });
});

// プロフィール遷移
document.querySelectorAll('.js-profile-link').forEach(el => {
    el.addEventListener('click', () => {
    const userId = el.dataset.userId;
    if (!userId) return;

    if (userId === document.body.dataset.meId) {
        location.href = '/profile';
    } else {
        location.href = `/profile/${userId}`;
    }
    });
});

// モーダル（画像拡大）
function openModal(src) {
    const modal = document.createElement('div');
    modal.classList.add('modal');
    modal.innerHTML = `
        <span class="modal-close" onclick="document.body.removeChild(this.parentElement)">×</span>
        <img src="${src}">
    `;
    document.body.appendChild(modal);
    modal.style.display = 'flex';
}


document.querySelectorAll('.like-button').forEach(button => {
    button.addEventListener('click', () => {
        button.classList.toggle('liked');
    });
});

document.querySelectorAll('.like-button').forEach(button => {
    button.addEventListener('click', () => {
        // 色切り替え
        button.classList.toggle('liked');

        // アニメーション付与
        button.classList.remove('animate');
        void button.offsetWidth; // 再描画トリガー
        button.classList.add('animate');
    });
});

//コメントを全表示させるためのボタンを表示&コメント全表示をやめさせるボタン
document.querySelectorAll('showMoreBtn').forEach(btn => {
    btn.addEventListener('click', function(){
        //ボタンのすぐ上にあるリストを取得
        const list = this.previousElementSibling;

        //クラスのON/OFFを切り替え
        list.classList.toggle('expanded');

        //今の状態に合わせて文字を変える
        if(list.classList.contains('expanded')){
            this.textContent = '閉じる';
        } else {
            this.textContent = 'コメント全表示';
        }
    })
})