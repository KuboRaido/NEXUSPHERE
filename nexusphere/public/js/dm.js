// dm-chat.js --- 会話ページ用（#chat-box があるページだけで動く）

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
  const map = new Map(oldList.map(m => [m.id, m]));
  for (const m of newList) map.set(m.id, m);
  return Array.from(map.values()).sort((a,b)=> a.timestamp - b.timestamp);
}

function renderMessages() {
  const chatBox = document.getElementById('chat-box');
  if (!chatBox) return;

  chatBox.querySelectorAll('.message-row').forEach(el => el.remove());

  const currentUserId = String(getMeId() ?? '');

  for (const msg of messages){
    const mine = (msg.from === currentUserId);

    const row = document.createElement('div');
    row.classList.add('message-row', mine ? 'from-me' : 'from-them');

    const img = document.createElement('img');
    img.className = 'msg-avatar';
    img.src = mine ? ME_ICON : PARTNER_ICON;
    img.alt = '';
    img.onerror = () => { img.src = DEFAULT_AVATAR; };

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
          img.src = att.url; img.alt = ''; img.style.maxWidth='200px'; img.style.borderRadius='8px';img.style.display='block';img.style.marginTop='6px';
          wrap.appendChild(img);
        }else if (att.type === 'video'){
          const v = document.createElement('video');
          v.src = att.url; v.controls = true; v.style.maxWidth='220px';v.style.borderRadius='8px'; v.style.display='block'; v.style.marginTop='6px';
          wrap.appendChild(v);
        }
      });
    bubble.appendChild(wrap);
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
  if (attachBtn && fileInput) attachBtn.addEventListener('click', () => fileInput.click());
});

//API
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
    attachment: m.attachments || [],
    timestamp: new Date(m.created_at),
    pending: false
  }));

  messages = mergeById(messages, newList);
  window.messages = messages;

  renderMessages();
}

async function sendMessage() {
  const meId = getMeId();
  const currentUserId = String(meId);
  const recipientRaw = String(document.getElementById('recipientId')?.value ?? '').trim();
  const input = document.getElementById('message-input');
  const text = String(input?.value ?? '').trim();
  const fileInput = document.getElementById('image-input');
  if (!text) return;

  //自分にメッセージを送れる
  let toId;
  if (recipientRaw === '' || recipientRaw.toLowerCase() === 'me') {
    toId = meId;
  } else {
    toId = Number.parseInt(recipientRaw, 10);
    if (!Number.isInteger(toId)) { alert('宛先は数字か "me"'); return; }
  }

  // 画面に一時表示＋メッセージにファイルを送付させる
  const tempId = 'tmp_' + Date.now();
  const tempAtt = [];
  if(fileInput?.files?.length){
    [...fileInput.files].forEach(f =>{
    const url = URL.createObjectURL(f);
    const type = f.type.startsWith('image/') ? 'image' : (f.type.startsWith('video/') ? 'video' : 'file');
    if(type === 'image' || type === 'video')tempAtt.push({type, url, pending:true});
  });
 }
  messages.push({id:tempId, from:String(getMeId()), to:String(toId), text, attachment:tempAtt, timestamp:new Date(), pending:true});
  renderMessages();


  try {
    await fetch('/sanctum/csrf-cookie', {credentials:'include'});
    const token = decodeURIComponent((document.cookie.match(/XSRF-TOKEN=([^;]+)/)||[])[1] || '');

    let res, resd;
    if(fileInput?.files?.length){
      const fd = new FormData();
      fd.append('to',toId);
      if(text)fd.append('text',text);
      [...fileInput.files].forEach(f => fd.append('files[]',f));
      res = await fetch('/api/v1/dmlist/dm', {
        method: 'POST',
        headers:{...(token ? {'X-XSRF-TOKEN':token} : {})},
        credentials:'include',
        body:fd
      });
    } else {
      res = await fetch('/api/v1/dmlist/dm',{
        method: 'POST',
        headers:{'Accept':'application/json','Content-Type':'application/json',...(token ? {'X-XSRF-TOKEN':token} : {})},
        credentials:'include',
        body:JSON.stringify({to:toId,text})
      });
    }
    try {resd = await res.json();} catch(_) {}
    if (!res.ok) throw new Error(resd?.message || `HTTP ${res.status}`);

    // サーバ確定値で置換
    const idx = messages.findIndex(m => m.id === tempId);
    if (idx !== -1) {
      messages[idx] = {
        id: resd.id,from:String(resd.from_id),to:String(resd.to_id),
        text: resd.text,attachment:resd.attachments || [],
        timestamp: new Date(resd.created_at), pending: false
    };
    renderMessages();
    }
    // URLの ?to= を送信先に合わせる
    if(fileInput)fileInput.value = '';
    if(input)input.value = '';//
  } catch (err) {
    alert(err.message || '送信に失敗しました');
  }

  
}

//起動
document.addEventListener('DOMContentLoaded', async () => {
  if (!document.getElementById('chat-box')) return;

  await fetch('/sanctum/csrf-cookie', { credentials: 'include' });

  const meId = getMeId();
  const qs = new URLSearchParams(location.search);
  const toParam = (qs.get('to') || '').trim();

  let partnerId;
  if (toParam === '' || toParam.toLowerCase() === 'me') {
    partnerId = meId;
  } else if (/^\d+$/.test(toParam)) {
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