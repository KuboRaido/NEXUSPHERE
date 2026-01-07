// dm-list.js --- 一覧ページ用（#dm-list があるページだけで動く）

let DEFAULT_AVATAR = window.DEFAULT_AVATAR_URL || '/images/default-avatar.png';

// ----- ユーティリティ（一覧用） -----
function escapeHtml(s){
  return String(s).replace(/[&<>"']/g, (c) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  }[c]));
}

function formatTime(iso) {
  if (!iso) return '';
  const d = new Date(iso);
  if (isNaN(d)) return '';

  // 時間を "HH:MM" の形式に整形（ゼロ埋め対応）
  const hours = d.getHours().toString().padStart(2, '0');
  const minutes = d.getMinutes().toString().padStart(2, '0');
  return `${hours}:${minutes}`;
}

//検索
let searchTimeout = null;

async function searchUsers(keyword){
  const searchResults = document.getElementById('search-results');
  if(!searchResults) return('存在しません');

  if(!keyword || keyword.trim() === ''){
    searchResults.innerHTML = '';
    searchResults.style.display = 'none';
    return;
  }

  try{
    const res = await fetch(`/api/v1/users/search?q=${encodeURIComponent(keyword)}`,{
      headers: {'Accept': 'application/json'},
      credentials: 'include',
    });

    if(!res.ok) throw new Error('検索に失敗しました');

    const users = await res.json();

    if(users.length === 0){
    searchResults.innerHTML = '<li class="empty">ユーザーが見つかりませんでした</li>';
    searchResults.style.display = 'block';
    return;
  }

  searchResults.innerHTML = '';
  searchResults.style.display = 'block';

  for(const user of users){
    const li = document.createElement('li');
    li.className = 'search-result-item';
    li.innerHTML = `
      <a class ="user-id" href="/dm?to=${user.user_id}">
        <img class="icon" src="${user.icon || DEFAULT_AVATAR}" alt="" onerror="this.src='${DEFAULT_AVATAR}'">
        <div class="search-content">
          <div class="search-name">${escapeHtml(user.name)}</div>
        </div>
      </a>
    `;
    searchResults.appendChild(li);
  }
 } catch(e){
  console.error(e);
  searchResults.innerHTML = '<li class="error">検索中にエラーが発生しました</li>';
  searchResults.style.display = 'block';
 }
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
      const unread = Number(it.unread_count || 0);
      const badge = unread > 0 ? `<span class="unread">${unread}</span>` : '';

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
          ${badge}
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

//検索バーのイベント
const searchInput = document.getElementById('search-input');
if(searchInput){
  searchInput.addEventListener('input', function(){
    const keyword = this.value.trim();

    //入力のたびに少し待ってから検索
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      searchUsers(keyword);
    }, 300);//0.3秒後に実行される関数
  });

  //カーソルが検索バーから外れたら検索結果を隠す
  searchInput.addEventListener('blur',function(){
    setTimeout(() => {
      const searchResults = document.getElementById('search-results');
      if(searchResults)searchResults.style.display = 'none';
    },30000);//0.2秒後に実行される関数
  });
//入力が空でなければ検索する
  searchInput.addEventListener('focus',function(){
    if(this.value.trim()){
      searchUsers(this.value.trim());
    }
  });
}

// ----- ここからグループDM作成用の追加 -----

async function loadFriendList(){
  const friendRoot = document.getElementById("modalFriendList");
  if(!friendRoot) return;

  friendRoot.innerHTML = "読み込み中...";

  try{
    const res = await fetch("/api/friends", {
      headers: {'Accept':'application/json'},
      credentials:'include'
    });

    const friends = await res.json();

    if(friends.length === 0){
      friendRoot.innerHTML = "友達がいません";
      return;
    }

    friendRoot.innerHTML = "";

    friends.forEach(f => {
      const div = document.createElement("div");
      div.className = "friend-item";

      div.innerHTML = `
        <input type="checkbox" value="${f.id}" class="modal-friend-check">
        ${escapeHtml(f.name)}
      `;

      friendRoot.appendChild(div);
    });

  }catch(e){
    console.error(e);
    friendRoot.innerHTML = "友達一覧の取得に失敗しました";
  }
}

function initDmModal(){

  const openPopup = document.getElementById("openPopupBtn");
  const closeModal = document.getElementById("closeModalBtn");
  const modal = document.getElementById("createDmModal");

  if(!openPopup || !closeModal || !modal) return;

  openPopup.addEventListener("click", () => {
    modal.classList.remove("hidden");
    loadFriendList();
  });

  closeModal.addEventListener("click", () => {
    modal.classList.add("hidden");
  });

  const createRoom = document.getElementById("createRoomBtn");
  if(createRoom){

    createRoom.addEventListener("click", async () => {

      const checks = document.querySelectorAll(".modal-friend-check:checked");

      let ids = [];
      checks.forEach(c => ids.push(c.value));

      if(ids.length === 0){
        alert("ユーザーを選択してください");
        return;
      }

      try{
        await fetch("/dm/createRoom", {
          method:"POST",
          headers:{
            "Content-Type":"application/json",
            "Accept":"application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content
          },
          credentials:'include',
          body: JSON.stringify({ user_ids: ids })
        });

        modal.classList.add("hidden");
        location.reload();

      }catch(e){
        console.error(e);
        alert("グループ作成に失敗しました");
      }

    });

  }

}

document.addEventListener("DOMContentLoaded", () => {
  initDmModal();
});

// ----- 追加ここまで -----
