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
        <img class="icon" src="${user.icon || DEFAULT_AVATAR}">
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
async function loaddmlist(isBackground = false){
  const listRoot = document.getElementById('dm-list');
  if(!listRoot) return;

  if(!isBackground) listRoot.innerHTML = '<li class="loading">読み込み中...</li>';

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

    const fallback = DEFAULT_AVATAR;

    // 初回のみ空にする（または全クリアしない）
    if(!isBackground) listRoot.innerHTML = '';
    
    for (const it of items){
      const isGroup = !!it.is_group;
      const partnerId = it.partner_id;
      // グループとユーザーでIDが被る可能性があるので接頭辞をつける
      const uniqueId = isGroup ? `group_${partnerId}` : `user_${partnerId}`;
      let li = document.getElementById(`dm-item-${uniqueId}`);

      let href = '';
      if(isGroup){
        href = `/dm?group_id=${partnerId}`;
      } else {
        href = `/dm?to=${partnerId}`;
      }
      const iconUrl = it.partner_icon ? it.partner_icon : DEFAULT_AVATAR;
      const unread = Number(it.unread_count || 0);

      if(li){
        // --- 既存要素の更新（差分のみ） ---
        // メッセージ更新
        const msgEl = li.querySelector('.chat-message');
        if(msgEl && msgEl.textContent !== (it.last_message || '')){
            msgEl.textContent = it.last_message || '';
        }

        // 時刻更新
        const timeEl = li.querySelector('.chat-meta');
        const newTimeStr = formatTime(it.last_time);
        if(timeEl && timeEl.getAttribute('datetime') !== (it.last_time || '')){
            timeEl.setAttribute('datetime', it.last_time || '');
             // 表示上の時刻文字列が変わった場合のみ書き換えでもよいが、単純代入でも軽い
            timeEl.textContent = newTimeStr;
        }

        // 未読バッジ更新
        let badgeEl = li.querySelector('.unread');
        if(unread > 0){
          if(!badgeEl){
             // 無ければ作る
            badgeEl = document.createElement('span');
            badgeEl.className = 'unread';
            li.querySelector('.dm-link').appendChild(badgeEl);
          }
          badgeEl.textContent = unread;
        } else {
          // 未読0なら消す
          if(badgeEl) badgeEl.remove();
        }

        // アイコン画像（変更検知が難しければ、基本そのままにするか onError再設定など）
        // 普通はURLが変わらない限りそのままでOK
        
      } else {
        // --- 新規作成 ---
        li = document.createElement('li');
        li.className = 'dm-list';
        li.id = `dm-item-${uniqueId}`; // ★後で探せるようにID付与

        const badgeHtml = unread > 0 ? `<span class="unread">${unread}</span>` : '';

        li.innerHTML = `
          <a class="dm-link" href="${href}">
            <img class="avatar" src="${iconUrl}" alt="" onerror="this.src='${fallback}'">
            <div class="chat-content">
              <div class="chat-name">${escapeHtml(it.partner_name || 'Unknown')}</div>
              <div class="chat-message">${escapeHtml(it.last_message || '')}</div>
            </div>
            <time class="chat-meta" datetime="${it.last_time || ''}">
              ${formatTime(it.last_time)}
            </time>
            ${badgeHtml}
          </a>
        `;
        listRoot.appendChild(li);
      }
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
  // 2秒ごとに更新（バックグラウンドモード）
  setInterval(() => loaddmlist(true), 2000);
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

//DMのグループ作成画面のuser一覧の取得
async function loadFriendList(){
  const friendRoot = document.getElementById("modalFriendList");
  if(!friendRoot) return;

  friendRoot.innerHTML = "読み込み中...";

  try{
    const res = await fetch("/api/v1/friends", {
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

//DMグループ作成
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
      //グループ名を取得
      const groupNameInput = document.getElementById("group_name");
      //グループ名が入力されていたら前後の空白を削除・名前が無ければ空白
      const groupName = groupNameInput ? groupNameInput.value.trim() : "";
      //アイコンを取得
      const groupIcon = document.getElementById("iconUpload");
      //グループに入れると選択した友達を取得
      const checks = document.querySelectorAll(".modal-friend-check:checked");

      //選択した友達を入れるための箱を作成
      let ids = [];
      //選択した友達を一人ずつ配列に追加
      checks.forEach(c => ids.push(c.value));

      if(!groupName){
        alert("グループ名を入力してください");
        return;
      }

      if(ids.length === 0){
        alert("ユーザーを選択してください");
        return;
      }

      //画像が含まれるためFormDataを使用
      const formData = new FormData();
      formData.append("group_name" , groupName);

      //選択したユーザーの数だけ繰り返しFormDataに入れる
      ids.forEach(id => formData.append("user_ids[]" , id));
      if(groupIcon && groupIcon.files[0]){
        formData.append("icon" , groupIcon.files[0]);
      }
      try{
          await fetch("/api/v1/dm/createRoom", {
          method:"POST",
          //送る内容は上記で作成したformData
          body: formData,
          //クッキー情報も一緒に送る　これが無いと誰から送られたものなのかわからないから
          credentials: 'include',
          //通信のメタ情報 X-CSRF-TOKEN:Laravelなどのフレームワークで必須の「セキュリティ通行手形」
          headers:{
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content
          }
          });

        modal.classList.add("hidden");
        location.reload();

      }catch(e){
        console.error(e);
        alert(e.message || "グループ作成に失敗しました");
      }

    });

  }

}

document.addEventListener("DOMContentLoaded", () => {
  initDmModal();
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
