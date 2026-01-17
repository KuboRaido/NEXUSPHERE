// グローバル状態
let messages = window.messages || [];
window.messages = messages;

let currentPartnerId = window.currentPartnerId || null;
window.currentPartnerId = currentPartnerId;

window.previewImage = function(e){
  const files = e?.target?.files;
  console.log(files)
}

let DEFAULT_AVATAR = window.DEFAULT_AVATAR_URL || '/images/default-avatar.png';
let ME_ICON = DEFAULT_AVATAR;
let PARTNER_ICON = DEFAULT_AVATAR;

async function ensureXsrfReady() {
  await fetch('/sanctum/csrf-cookie', { credentials: 'include' });
}

function getXsrfHeader() {
  const m = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
  if (!m) return {};
  const token = decodeURIComponent(m[1]);
  return { 'X-XSRF-TOKEN': token };
}

function getMeId() {
  const el = document.getElementById('currentUserId');
  const v1 = el?.value;
  if (v1 && Number.isInteger(+v1)) return +v1;

  const meta = document.querySelector('meta[name="current-user-id"]');
  const v2 = meta?.content;
  if (v2 && Number.isInteger(+v2)) return +v2;

  if (Number.isInteger(window.CURRENT_USER_ID)) return window.CURRENT_USER_ID;

  console.warn('ログインユーザーIDが取得できません');
  return NaN;
}

function setCurrentPartner(id) {
  currentPartnerId = id;
  window.currentPartnerId = id;
  const recipientInput = document.getElementById('recipientId');
  if (recipientInput) recipientInput.value = String(id);
}

function mergeById(oldList, newList) {
  const normalize = (entry) => {
    if (!entry) return entry;
    if (!(entry.timestamp instanceof Date)) {
      entry.timestamp = entry.timestamp ? new Date(entry.timestamp) : new Date();
    }
    return entry;
  };

  const map = new Map(oldList.map(m => [m.id, normalize(m)]));
  for (const m of newList) map.set(m.id, normalize(m));
  return Array.from(map.values()).sort((a,b)=> a.timestamp - b.timestamp);
}

function renderMessages() {
  const chatBox = document.getElementById('chat-box');
  if (!chatBox) return;

  chatBox.querySelectorAll('.message-row').forEach(el => el.remove());


  const currentUserId = String(getMeId() ?? '');
  const makeStatus = (mine, read) => (!mine ? '' : (read ? '既読' : '未読'));

  for (const msg of messages){
    const mine = (msg.from === currentUserId);
    const status = makeStatus(mine, msg.isRead);

    const row = document.createElement('div');
    row.classList.add('message-row', mine ? 'from-me' : 'from-them');

    const img = document.createElement('img');
    img.className = 'msg-avatar';
    img.src = mine ? ME_ICON : PARTNER_ICON;
    img.alt = '';
    img.onerror = () => { img.src = DEFAULT_AVATAR; };
    // アイコンを押したらプロフィールに飛べるようにする
    const profileId =String(currentPartnerId);
    // 相手のiconの画像にリンクを追加
    img.addEventListener('click', (e) => {
      e.preventDefault();
      if(!profileId) return;
      if(profileId === String(getMeId())){
        location.href = `/profile`;
      } else  {
        location.href = `/profile/${profileId}`;
      }
    })
    const bubble = document.createElement('div');
    bubble.className = 'message-bubble';
    if(msg.text)bubble.textContent = msg.text;

    //動画と画像を画面に表示する
  if(Array.isArray(msg.attachments) && msg.attachments.length){
      const wrap = document.createElement('div');
      wrap.className = 'att-wrap';
      msg.attachments.forEach( att =>{
        if(att.type === 'image'){
          const img = document.createElement('img');
        img.src = att.url;
        img.alt = '';
        img.style.borderRadius = '8px';
        img.style.display = 'block';
        img.style.margin = '6px auto 0 auto'; // 上余白6px、左右中央
        img.style.maxWidth = '100%';   // 吹き出しの幅に収める
        img.style.height = 'auto';     // 縦横比を維持
        img.style.objectFit = 'cover'; // 中央寄せのまま
        wrap.appendChild(img);
        }else if (att.type === 'video'){
          const v = document.createElement('video');
            v.src = att.url;
            v.controls = true;
            v.style.borderRadius = '8px';
            v.style.display = 'block';
            v.style.margin = '6px auto 0 auto'; // 上余白6px、左右中央
            v.style.maxWidth = '100%';   // 吹き出しの幅に収める
            v.style.height = 'auto';     // 縦横比を維持
            v.style.objectFit = 'cover'; // 余白を埋める
            wrap.appendChild(v);
        }
      });
    bubble.appendChild(wrap);
  }

  if(status){
    const label = document.createElement('span');
    label.className = 'read-status';
    label.textContent = status;
    bubble.appendChild(label);
  }
    if (mine) {
      row.appendChild(bubble);
      row.appendChild(img);
    } else {
      row.appendChild(img);
      row.appendChild(bubble);
    }
    chatBox.appendChild(row);
  }

  chatBox.scrollTop = chatBox.scrollHeight; 
}

document.addEventListener('DOMContentLoaded',()=>{
  const attachBtn = document.getElementById('attach-btn');
  const fileInput = document.getElementById('image-input');
  if (attachBtn && fileInput){
    attachBtn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      fileInput.click();
    });
  } ;
});

//API 1対1のdmの会話履歴取得
async function loadConversation(currentPartnerId) {
  if (!Number.isInteger(currentPartnerId)) return;
  setCurrentPartner(currentPartnerId);

  const res = await fetch(`/api/v1/dm/${currentPartnerId}`, {
    headers: { 'Accept': 'application/json' },
    credentials: 'include',
  });
  if(!res.ok){
    console.error(`GET /api/v1/dm/${currentPartnerId} failed`, res.status);
    throw new Error(`会話取得エラー: ${res.status}`);
  }
  const json = await res.json();

  ME_ICON = json?.participants?.me?.avatar || ME_ICON;
  PARTNER_ICON = json?.participants?.partner?.avatar || PARTNER_ICON;

  const newList = (json.dms || []).map(m => ({
    id: m.id,
    from: String(m.from_id),
    to:   String(m.to_id),
    text: m.text,
    attachments: m.attachments || [],
    timestamp: new Date(m.created_at),
    pending: false,
    isRead: Boolean(m.is_read)
  }));

  await ensureXsrfReady();
  await fetch(`/api/v1/dm/${currentPartnerId}/read`,{method:'POST',headers:{'Accept':'application/json', ...getXsrfHeader()},credentials:'include'}).catch(()=>{});

  
  document.addEventListener('visibilitychange', async()=>{
    if(document.visibilityState === 'visible' && Number.isInteger(currentPartnerId)){
      await ensureXsrfReady();
      fetch(`/api/v1/dm/${currentPartnerId}/read`,{method:'POST',headers:{'Accept':'application/json', ...getXsrfHeader()},credentials:'include'}).catch(()=>{});
    }
  });
  messages = mergeById(messages, newList);
  window.messages = messages;

  renderMessages();
}

// Circle内のDMの会話履歴取得API
async function loadCircleConversation(circle_id){
  const res  = await fetch(`/api/v1/circle/${circle_id}/dm`, {
    headers: {Accept: 'application/json' },
    credentials: 'include',
  });

  if(!res.ok) throw new Error(`会話取エラー: ${res.status}`);
  const json = await res.json();
  messages = mergeById(messages, json.dms || []).map(m => ({
    id: m.id,
    from: String(m.from_id),
    to:   '',
    text: m.text,
    attachments: m.attachments || [],
    timestamp: new Date(m.created_at),
    pending: false,
    isRead: Boolean(m.is_read),
  }));
  renderMessages();
}

// Groupの会話履歴取得API
async function loadGroupConversation(group_id){
  const res = await fetch(`/api/v1/group/${group_id}/dm`, {
    headers: {Accept: 'application/json' },
    credentials: 'include',
  });

  if(!res.ok) throw new Error(`会話ログエラー: ${res.status}`);
  const json = await res.json();
  messages = mergeById(messages, json.dms || []).map(m => ({
    id: m.id,
    from: String(m.from_id),
    to:   '',
    text: m.text,
    attachments: m.attachments || [],
    timestamp: new Date(m.created_at),
    pending: false,
    isRead: Boolean(m.is_read),
  }));
  renderMessages();
}

async function sendMessage() {
  const meId = getMeId();
  const fd = new FormData();
  const recipientRaw = String(document.getElementById('recipientId')?.value ?? '').trim();
  const input = document.getElementById('message-input');
  const fileInput = document.getElementById('image-input');
  const circle = Number(document.getElementById('circle_id')?.value ?? '');
  const hasCircle = Number.isInteger(circle) && circle > 0;
  if(Number.isInteger(circle)) fd.append('circle_id', circle);

  // グループIDの取得
  const qs = new URLSearchParams(location.search);
  const groupParam = qs.get('group_id');
  const hasGroup = groupParam && /^\d+$/.test(groupParam);
  if(hasGroup) fd.append('group_id', groupParam);

  const text = (input?.value ?? '').trim();
  const files = fileInput?.files ?  Array.from(fileInput.files) : [];
  if(input) input.value = '';
  const hadFiles = files.length > 0;
  if (fileInput) fileInput.value = '';

  if (!text && !hadFiles) return;
  //自分にメッセージを送れる
  let toId;
  if(recipientRaw === 'circle'){
    toId = circle;
  } else if(hasGroup){
    toId = parseInt(groupParam, 10);
  } else if (recipientRaw === '' || recipientRaw.toLowerCase() === 'me') {
    toId = meId;
  } else {
    toId = Number.parseInt(recipientRaw, 10);
    if (!Number.isInteger(toId)) { alert('宛先は数字か "me"'); return; }
  }

  // 画面に一時表示＋メッセージにファイルを送付させる
  const tempId = 'tmp_' + Date.now();
  const tempAtt = [];
  if(hadFiles){
    files.forEach(f =>{
    const url = URL.createObjectURL(f);
    const type = f.type.startsWith('image/') ? 'image' : (f.type.startsWith('video/') ? 'video' : 'file');
    if(type === 'image' || type === 'video')tempAtt.push({type, url, pending:true});
  });
  }
  messages.push({id:tempId, from:String(getMeId()), to:String(toId), text, attachments:tempAtt, timestamp:new Date(), pending:true, isRead:false});
  renderMessages();


  try {
    await fetch('/sanctum/csrf-cookie', {credentials:'include'});
    const token = decodeURIComponent((document.cookie.match(/XSRF-TOKEN=([^;]+)/)||[])[1] || '');

    if(hasCircle){
        await fetch(`/api/v1/circle/${circle}/dm`, {
        method:'POST',
        headers: { 'Accept':'application/json', ...(token ? {'X-XSRF-TOKEN': token} : {}) },
        credentials:'include'
      });
    } else if(hasGroup){
      await fetch(`/api/v1/group/${groupParam}/dm`, {
        method:'POST',
        headers: { 'Accept':'application/json', ...(token ? {'X-XSRF-TOKEN': token} : {}) },
        credentials:'include'
      });
    } else {
        await fetch(`/api/v1/dm/${currentPartnerId}/read`, {
          method:'POST',
          headers: { 'Accept':'application/json', ...(token ? {'X-XSRF-TOKEN': token} : {}) },
          credentials:'include'
        });
    }

    const previewContainer = document.getElementById('preview-area');
    if(previewContainer) previewContainer.innerHTML = '';

    let res, resd;

    const isCircleMode = Number.isInteger(circle) && circle > 0;
    const isGroupMode = hasGroup;

    if (hadFiles) {
      if(isCircleMode){
        fd.append('circle_id', circle);
      } else if(isGroupMode){
        fd.append('group_id', groupParam);
      } else {
        fd.append('to', toId);
      }
      if(text)fd.append('text',text);
      files.forEach(f => fd.append('files[]',f));
      res = await fetch('/api/v1/dm', {
        method: 'POST',
        headers:{...(token ? {'X-XSRF-TOKEN':token} : {})},
        credentials:'include',
        body:fd
      });
    } else {
      let payload;
      if(isCircleMode) payload = {circle_id: circle, text};
      else if(isGroupMode) payload = {group_id: groupParam, text};
      else payload = {to: toId, text};

      res = await fetch('/api/v1/dm',{
        method: 'POST',
        headers:{'Accept':'application/json','Content-Type':'application/json',...(token ? {'X-XSRF-TOKEN':token} : {})},
        credentials:'include',
        body: JSON.stringify(payload),
      });
    }
    try {resd = await res.json();} catch(_) {}
    if (!res.ok) throw new Error(resd?.message || `HTTP ${res.status}`);

    // サーバ確定値で置換
    const idx = messages.findIndex(m => m.id === tempId);
    if (idx !== -1) {
      messages[idx] = {
        id: resd.id,from:String(resd.from_id),to:String(resd.to_id),
        text: resd.text,attachments:resd.attachments || [],
        timestamp: new Date(resd.created_at), pending: false
    };
    renderMessages();
    }
  } catch (err) {
    alert(err.message || '送信に失敗しました');
  }

  
}

//起動
document.addEventListener('DOMContentLoaded', async () => {
  if (!document.getElementById('chat-box')) return;

  await fetch('/sanctum/csrf-cookie', { credentials: 'include' });

  const meId             = getMeId();
  const qs               = new URLSearchParams(location.search);
  const toParam          = (qs.get('to') || '').trim();
  const fileInput        = document.getElementById('image-input');
  const previewContainer = document.getElementById('preview-area');
  const circle           = Number(document.getElementById('circle_id')?.value ?? '');
  const group            = (qs.get('group_id') || '').trim();
  const isCircleMode     = Number.isInteger(circle) && circle > 0;

  if(fileInput && previewContainer){
    fileInput.addEventListener('change', () => {
      previewContainer.innerHTML = '';
      if(fileInput.files.length === 0){
        return;
      }

      Array.from(fileInput.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
          const img = document.createElement('img');
          img.src = e.target.result;
          img.className = 'preview-image';
          previewContainer.appendChild(img);
        }
        reader.readAsDataURL(file);
      })
    })
  }

  let currentPartnerId;
  // group_xx 形式に対応
  const isGroupMode = (group !== '');
  let currentGroupId = null;

  if (isGroupMode) {
      currentGroupId = parseInt(group,10);
  } else if (toParam === '' || toParam.toLowerCase() === 'me') {
    currentPartnerId = meId;
  } else if (/^\d+$/.test(toParam)) {
    currentPartnerId = parseInt(toParam, 10);
  } else {
    currentPartnerId = meId;
  }

  // setCurrentPartner は 1対1 の場合のみ設定（フォーム用）
  if (currentPartnerId) {
    setCurrentPartner(currentPartnerId);
  }

  if (isCircleMode) {
    await loadCircleConversation(circle);
    setInterval(() => loadCircleConversation(circle), 1000);
  } else if (isGroupMode && Number.isInteger(currentGroupId)) {
    // グループチャット読み込み
    await loadGroupConversation(currentGroupId);
    setInterval(() => loadGroupConversation(currentGroupId), 1000);
  } else {
    await loadConversation(currentPartnerId);
    setInterval(() => loadConversation(currentPartnerId), 1000);
  }
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