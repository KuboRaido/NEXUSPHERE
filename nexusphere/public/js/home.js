// いいね非同期
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    document.querySelectorAll('.js-like-form').forEach((form) => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn = form.querySelector('.like-button');
            const countEl = form.querySelector('.like-count');
            const icon = form.querySelector('.like-icon');
            if (!btn || !countEl || !icon) return;

            btn.disabled = true;
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const data = await response.json();

                countEl.textContent = data.like_count;
                btn.classList.toggle('liked', !!data.liked);
                icon.classList.toggle('fa-solid', !!data.liked);
                icon.classList.toggle('fa-regular', !data.liked);

                btn.classList.remove('animate');
                void btn.offsetWidth;
                btn.classList.add('animate');
            } catch (_) {
                alert('いいねの更新に失敗しました');
            } finally {
                btn.disabled = false;
            }
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


//コメントを全表示させるためのボタンを表示&コメント全表示をやめさせるボタン
document.querySelectorAll('.showMoreBtn').forEach(btn => {
    btn.addEventListener('click', function(){
        //ボタンのすぐ上にあるリストを取得
        const list = this.previousElementSibling;

        //クラスのON/OFFを切り替え
        list.classList.toggle('expanded');

        //今の状態に合わせて文字を変える
        if(list.classList.contains('expanded')){
            this.textContent = '閉じる';
        } else {
            this.textContent = '全てのコメントを見る';
        }
    })
})