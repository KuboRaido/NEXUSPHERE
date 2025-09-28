// dm-chat.js --- 会話ページ用（#chat-box があるページだけで動く）

// === グローバル状態（会話専用） ===
let messages = window.messages || [];
window.messages = messages;

let currentPartnerId = window.currentPartnerId || null;
window.currentPartnerId = currentPartnerId;

let DEFAULT_AVATAR = window.DEFAULT_AVATAR_URL || '/images/default-avatar.png';
let ME_ICON = DEFAULT_AVATAR;
let PARTNER_ICON = DEFAULT_AVATAR;

// ----- ユーティリティ（会話用） -----
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
    img.onerror = () => { img.src = DEFAULT_AVATAR; };

    const bubble = document.createElement('div');
    bubble.className = 'message-bubble';
    bubble.textContent = msg.text;

    if (mine) {
      row.appendChild(bubble);
      row.appendChild(img);
    } else {
      row.appendChild(img);
      row.appendChild(bubble);
    }
    chatBox.appendChild(row);
  }

  chatBox.scrollTop = chatBox.scrollHeight; // 常に最下部へ
}

// ----- API（会話） -----
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
  if (!text) return;

  let toId;
  if (recipientRaw === '' || recipientRaw.toLowerCase() === 'me') {
    toId = meId;
  } else {
    toId = Number.parseInt(recipientRaw, 10);
    if (!Number.isInteger(toId)) { alert('宛先は数字か "me"'); return; }
  }

  // 楽観表示
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

    // ★送信API：あなたの現コードに合わせて /api/v1/dmlist/dm を使用
    //   バックが /api/v1/dm/send なら、ここを差し替えてください。
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

    // サーバ確定値で置換
    const idx = messages.findIndex(m => m.id === tempId);
    if (idx !== -1) {
      messages[idx] = { ...tempMsg, id: realId, timestamp: realTime, pending: false };
      renderMessages();
    }

    // URLの ?to= を送信先に合わせる
    if (currentPartnerId !== toId) {
      history.replaceState(null, '', `?to=${toId}`);
    }
  } catch (err) {
    const idx = messages.findIndex(m => m.id === tempId);
    if (idx !== -1) { messages[idx].error = true; messages[idx].pending = false; renderMessages(); }
    alert(err.message);
  }
}

// ----- 起動（会話ページのみ） -----
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