
//会話ページ用
document.addEventListener('DOMContentLoaded', async () => {

  if (!document.getElementById('chat-box'))return;
  await fetch('/sanctum/csrf-cookie',{credentials: 'include'});

  const meId = getMeId();

  const qs = new URLSearchParams(location.search);
  const toParam = (qs.get('to') || '').trim();

  let partnerId;
  if (toParam === '' || toParam.toLowerCase() === 'me'){
    partnerId = meId; 
  } else if (/^\d+$/.test(toParam)){
    partnerId = parseInt(toParam, 10); 
  } else {
    partnerId = meId;
  }

  setCurrentPartner(partnerId);
  await loadConversation(partnerId);

  // タッチ端末かどうか
  const isTouch = window.matchMedia('(pointer: coarse)').matches;

  // フォーム送信（モバイル主役）
  const form = document.getElementById('chat-form');
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      await sendMessage();
    });
  }

  // PCだけ Enter2回 / Ctrl+Enter
  let lastEnterTime = 0;
  const inputEl = document.getElementById('message-input');
  if (inputEl) {
    inputEl.addEventListener('keydown', (event) => {
      if (event.isComposing) return;

      if (!isTouch && event.ctrlKey && event.key === 'Enter') {
        event.preventDefault();
        sendMessage();
        return;
      }

      //PCのみ二度押し
      if (!isTouch && event.key === 'Enter') {
        const now = Date.now();
        if (now - lastEnterTime < 500) {
          event.preventDefault();
          sendMessage();
          lastEnterTime = 0;
        } else {
          lastEnterTime = now;
        }
      }
    });
  }
});

// === グローバル状態 ===
let messages = window.messages || [];
window.messages = messages;

let currentPartnerId = window.currentPartnerId || null;
window.currentPartnerId = currentPartnerId;


function getMeId() {
  const el = document.getElementById('currentUserId');
  const v1 = el?.value;
  if (v1&& Number.isInteger(+v1)) return +v1;

  const meta = document.querySelector('meta[name="current-user-id"]');
  const v2 = meta?.content;
  if (v2 && Number.isInteger(+v2)) return +v2;

  if (Number.isInteger(window.CURRENT_USER_ID)) return window.CURRENT_USER_ID;

  console.warn('ログインユーザーIDが取得できません')
  return NaN;
}

function setCurrentPartner(id) {
  currentPartnerId = id;
  window.currentPartnerId = id;
  const recipientInput = document.getElementById('recipientId');
  if (recipientInput) recipientInput.value = String(id);
}

let DEFAULT_AVATAR = window.DEFAULT_AVATAR_URL || '/images/default-avatar.png';
let ME_ICON = DEFAULT_AVATAR;
let PARTNER_ICON = DEFAULT_AVATAR;

//会話読み込み
async function loadConversation(partnerId) {
  if (!Number.isInteger(partnerId)) return;
  setCurrentPartner(partnerId);

  
  const res = await fetch(`/api/v1/dmlist/dm/${partnerId}`, {
    headers: { 'Accept': 'application/json' },
    credentials: 'include',
  });
  const json = await res.json();

  ME_ICON = json?.participants?.me?.avatar || ME_ICON;
  PARTNER_ICON = json?.participants?.partner?.avatar || PARTNER_ICON;
  
  const newList = (json.dms || []).map(m => ({
    id: m.id,
    from: String(m.from_id),
    to:   String(m.to_id),
    text: m.text,
    timestamp: new Date(m.created_at),
    pending: false
  }));

  messages = mergeById(messages, newList)
  window.messages = messages;

  renderMessages();
}

//描画
function renderMessages() {
  const chatBox = document.getElementById('chat-box');
  if (!chatBox) return;

  const currentUserId = String(getMeId() ?? '');
  chatBox.innerHTML = '';

  for (const msg of messages){
    const mine = (msg.from === currentUserId);

    const row = document.createElement('div');

    row.classList.add('message-row', mine ? 'from-me' : 'from-them');

    const img = document.createElement('img');
    img.className = 'msg-avatar';
    img.src = mine ? ME_ICON : PARTNER_ICON;
    img.alt = '';
    img.onerror = () => {img.src = DEFAULT_AVATAR;};
    
    const bubble = document.createElement('div')
    bubble.className = 'message-bubble';
    bubble.textContent = msg.text;


    if(mine){
      row.appendChild(bubble);
      row.appendChild(img)
    }else{
      row.appendChild(img);
      row.appendChild(bubble);
    }
     chatBox.appendChild(row);
  }

  chatBox.scrollTop = chatBox.scrollHeight; // 常に最下部へ
}

function mergeById(oldList, newList){
  const map = new Map(oldList.map(m => [m.id, m]));
  for (const m of newList) map.set(m.id, m);
  return Array.from(map.values()).sort((a,b)=>a.timestamp - b.timestamp); 
}

//送信
async function sendMessage() {
  const meId = getMeId();
  const currentUserId = String(meId); // 自分のID（文字列化）
  const recipientRaw = String(document.getElementById('recipientId')?.value ?? '').trim();
  const input = document.getElementById('message-input');
  const text = String(input?.value ?? '').trim();
  if (!text) return;

  let toId;
  if (recipientRaw === '' || recipientRaw.toLowerCase() === 'me') {
    toId = meId; // 自分宛て
  } else {
    toId = Number.parseInt(recipientRaw, 10);
    if (!Number.isInteger(toId)) { alert('宛先は数字か "me"'); return; }
  }

  
  const tempId = 'tmp_' + Date.now();
  const tempMsg = {
    id: tempId,
    from: currentUserId,
    to:   String(toId),
    text,
    timestamp: new Date(),
    pending: true
  };
  messages.push(tempMsg);
  renderMessages();
  if (input) input.value = '';

  try {
    const token = decodeURIComponent((document.cookie.match(/XSRF-TOKEN=([^;]+)/)||[])[1] || '');

    const res = await fetch('/api/v1/dmlist/dm', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(token ? {'X-XSRF-TOKEN' : token} : {})
      },
      credentials: 'include',
      body: JSON.stringify({ to: toId, text })
    });
    const json = await res.json();
    if (!res.ok) throw new Error(json.message || '送信に失敗しました');

    const realId = json?.id ?? Date.now();
    const realTime = json?.created_at ? new Date(json.created_at) : new Date();

    //サーバの確定値で置換
    const idx = messages.findIndex(m => m.id === tempId);
    if (idx !== -1) {
      messages[idx] = { ...tempMsg, id: realId, timestamp: realTime, pending: false };
      renderMessages();
    }

    //送信先の会話を正で再読込
    if (currentPartnerId !== toId) {
      history.replaceState(null, '', `?to=${toId}`);
    }
    

  } catch (err) {
    const idx = messages.findIndex(m => m.id === tempId);
    if (idx !== -1) { messages[idx].error = true; messages[idx].pending = false; renderMessages(); }
    alert(err.message);
  }
}

async function loadDmList(){
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
      li.className = 'dm-item';
      li.innerHTML = `
        <a class="dm-link" href="${href}">
         <img class="dm-avatar" src="${icon}" alt="" onerror="this.src='${fallback}'">
          <div class="dm-meta">
           <div class="dm-name">${escapeHtml(it.partner_name || 'Unknown')}</div>
           <div class="dm-last">${escapeHtml(it.last_message || '')}</div>
          </div>
          <time class="dm-time" datetime="${it.last_time || ''}">
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

//一覧ページ用
document.addEventListener('DOMContentLoaded',async () => {
  const listRoot = document.getElementById('dm-list');
  if(!listRoot) return;

  await fetch('/sanctum/csrf-cookie',{credentials:'include'});
  await loadDmList();
});

function escapeHtml(s){
  return String(s).replace(/[&<>"']/g, (c) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  }[c]));
}

function formatTime(iso){
  if(!iso) return '';
  const d = new Date(iso);
  return isNaN(d) ?'': d.toLocaleString();
}
