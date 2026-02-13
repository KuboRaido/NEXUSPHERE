document.addEventListener("DOMContentLoaded", function() {
    const toggleBtn = document.querySelector(".portfolio-toggle");
    const content   = document.querySelector(".portfolio-content");

    const trigger       = document.getElementById("logout-trigger");
    const confirmLogout = document.getElementById("logout-confirm");
    const yes           = document.getElementById("logout-yes");
    const no            = document.getElementById("logout-no");
    const form          = document.getElementById("logout-form");

    if (trigger && confirmLogout && yes && no && form) {
    const showConfirm = () => {
        confirmLogout.hidden = false;
        confirmLogout.style.display = "grid";
    };

    const hideConfirm = () => {
        confirmLogout.hidden = true;
        confirmLogout.style.display = "none";
    };

    hideConfirm();

    trigger.addEventListener("click", showConfirm);
    no.addEventListener("click", hideConfirm);
    yes.addEventListener("click", () => form.submit());

    confirmLogout.addEventListener("click", (e) => {
        if (e.target === e.currentTarget) hideConfirm();
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && !confirmLogout.hidden) hideConfirm();
    });
    }

  // ▼ ポートフォリオ開閉（ここが重要）
    toggleBtn.addEventListener("click", () => {
    content.classList.toggle("is-open");

    toggleBtn.innerHTML = content.classList.contains("is-open")
        ? '<i class="fa-solid fa-chevron-up"></i>'
        : '<i class="fa-solid fa-chevron-down"></i>';
    });
});

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
