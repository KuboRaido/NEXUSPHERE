//いいねした人をダイアログで表示
document.addEventListener('DOMContentLoaded', () => {
    let modal = document.getElementById('like-users-modal');
    if (!modal) return;

    const listEl = modal.querySelector('#like-users-list');
    const closeBtn = modal.querySelector('#like-users-close');
    if (!listEl || !closeBtn) return;

    const openModal = () => modal.classList.add('is-open');
    const closeModal = () => {
        modal.classList.remove('is-open');
        listEl.innerHTML = '';
    };

    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    const toIconUrl = (icon) => {
        if (!icon) return '/images/default-avatar.png';
        if (icon.startsWith('http') || icon.startsWith('/')) return icon;
        return `/storage/icons/${icon}`;
    };

    document.addEventListener('click', async (e) => {
        const trigger = e.target.closest('.js-like-users-trigger');
        if (!trigger) return;

        const url = trigger.dataset.url;
        if (!url) return;

        try {
            const res = await fetch(url, {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const raw = await res.json();
            const rows = Array.isArray(raw) ? raw : (raw.data ?? []);
            const users = rows.map((row) => row.user ? ({
                user_id: row.user.user_id,
                name: row.user.name,
                icon: row.user.icon,
            }) : row);

            if (users.length === 0) {
                listEl.innerHTML = '<li class="like-user-empty">まだいいねはありません</li>';
            } else {
                listEl.innerHTML = users.map((u) => `
                    <li class="like-user-row">
                        <a href="/profile/${u.user_id}" class="like-user-link">
                            <img src="${toIconUrl(u.icon)}" alt="" class="user-icon">
                            <span>${u.name ?? 'ユーザー'}</span>
                        </a>
                    </li>
                `).join('');
            }

            openModal();
        } catch (err) {
            console.error(err);
            alert('いいねしたユーザー一覧の取得に失敗しました');
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

//コメント投稿・非同期・fetch
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    document.querySelectorAll('.comment-form').forEach((form) => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const cmt = form.querySelector('.comment-input');
            if (!cmt) return;

            const body = new FormData(form);
            cmt.disabled = true;
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body,
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const data = await response.json();

                const commentList = form.closest('.post').querySelector('.comment_list');
                commentList.insertAdjacentHTML('afterbegin',data.html);

                form.reset();

            } catch (_) {
                alert('コメントの表示に失敗しました');
            } finally {
                cmt.disabled = false;
            }
        });
    });
});

//投稿削除ボタン
document.addEventListener("DOMContentLoaded", function() {
    const deleteTriggers = document.querySelectorAll(".delete-post-trigger");

    if (deleteTriggers.length > 0) {
        deleteTriggers.forEach((postTrigger) => {
            const postId = postTrigger.dataset.postId;
            const confirmPost = document.getElementById(`delete-post-confirm-${postId}`);
            const postNo = confirmPost ? confirmPost.querySelector(".delete-post-no") : null;

            if (confirmPost && postNo) {
                const showConfirm = () => {
                    confirmPost.hidden = false;
                    // confirmPost.style.display = "grid"; // CSSのflex設定を優先するため削除
                };

                const hideConfirm = () => {
                    confirmPost.hidden = true;
                    // confirmPost.style.display = "none"; // hidden属性だけで制御するため削除
                };

                // 初期状態を非表示に
                hideConfirm();

                postTrigger.addEventListener("click", showConfirm);
                postNo.addEventListener("click", hideConfirm);

                confirmPost.addEventListener("click", (e) => {
                    if (e.target === e.currentTarget) hideConfirm();
                });
            }
        });

        // ESCキーで全てのモーダルを閉じる
        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape") {
                document.querySelectorAll(".delete-post-confirm").forEach(modal => {
                    modal.hidden = true;
                    // インラインスタイルを削除してCSSクラスの設定が適用されるようにする
                    modal.style.display = ""; 
                });
            }
        });
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