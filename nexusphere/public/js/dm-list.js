// dm-list.js --- 一覧ページ用（#dm-list があるページだけで動く）

let DEFAULT_AVATAR = window.DEFAULT_AVATAR_URL || '/images/default-avatar.png';

// ----- ユーティリティ（一覧用） -----
function escapeHtml(s){
  return String(s).replace(/[&<>"']/g, (c) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  }[c]));
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('search-input');
  const dmList = document.getElementById('dm-list');

  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const keyword = this.value.toLowerCase();
      const items = dmList.querySelectorAll('li');

      items.forEach(item => {
        const nameElement = item.querySelector('.name');
        if (nameElement) {
          const nameText = nameElement.textContent.toLowerCase();
          item.style.display = nameText.includes(keyword) ? '' : 'none';
        }
      });
    });
  }
});


function formatTime(iso) {
  if (!iso) return '';
  const d = new Date(iso);
  if (isNaN(d)) return '';

  // 時間を "HH:MM" の形式に整形（ゼロ埋め対応）
  const hours = d.getHours().toString().padStart(2, '0');
  const minutes = d.getMinutes().toString().padStart(2, '0');
  return `${hours}:${minutes}`;
}

// ----- API & 描画（一覧） -----
async function loaddmlist(){
  const listRoot = document.getElementById('dm-list');
  if(!listRoot) return;

  listRoot.innerHTML = '<li class="loading">読み込み中...</li>';

  try{
    const res = await fetch('/api/v1/dmlist',{
      headers: {'Accept': 'application/json'},
      credentials: 'include',
    });
    const json = await res.json();
    if (!res.ok) throw new Error(json.message|| 'DM一覧の取得に失敗しました');

    const items = Array.isArray(json) ? json : (json.data ?? json.dms ?? []);
    if(!items.length){
      listRoot.innerHTML = '<li class="empty">まだ会話がありません</li>';
      return;
    }

    const tpl = listRoot.dataset.chatUrlTemplate || '/dm?to=__ID__';
    const fallback = DEFAULT_AVATAR;

    listRoot.innerHTML = '';
    for (const it of items){
      const href = tpl.replace('__ID__',encodeURIComponent(it.partner_id));
      const icon = it.partner_icon || '/images/default-avatar.png';

      const li = document.createElement('li');
      li.className = 'dm-list';
      li.innerHTML = `
        <a class="dm-link" href="${href}">
          <img class="avatar" src="${icon}" alt="" onerror="this.src='${fallback}'">
          <div class="chat-content">
            <div class="chat-name">${escapeHtml(it.partner_name || 'Unknown')}</div>
            <div class="chat-message">${escapeHtml(it.last_message || '')}</div>
          </div>
          <time class="chat-meta" datetime="${it.last_time || ''}">
            ${formatTime(it.last_time)}
          </time>
        </a>
      `;
      listRoot.appendChild(li);
    }
  } catch (e) {
    console.error(e);
    listRoot.innerHTML = '<li class="error">一覧の読み込みに失敗しました</li>';
  }
}

// ----- 起動（一覧ページのみ） -----
document.addEventListener('DOMContentLoaded', async () => {
  const listRoot = document.getElementById('dm-list');
  if(!listRoot) return;

  await fetch('/sanctum/csrf-cookie',{credentials:'include'});
  await loaddmlist();
});
