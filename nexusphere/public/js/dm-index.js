// 仮データ
const conversations = [
  { id: 1, name: "佐藤 花子", lastMessage: "了解です😊", time: "10:15", unread: 2 },
  { id: 2, name: "開発チーム", lastMessage: "デプロイ完了しました", time: "09:40", unread: 0 },
  { id: 3, name: "田中 一郎", lastMessage: "今どこ？", time: "昨日", unread: 0 },
  { id: 4, name: "中嶋さん", lastMessage: "資料送ります", time: "月曜", unread: 1 }
];

const chatBox = document.getElementById("chat-box");

function renderConversations() {
  chatBox.innerHTML = "";
  conversations.forEach(c => {
    const item = document.createElement("div");
    item.className = "chat-item";

    // アバター（名前の頭文字）
    const avatar = document.createElement("div");
    avatar.className = "avatar";
    avatar.textContent = c.name.charAt(0);

    // コンテンツ部分
    const content = document.createElement("div");
    content.className = "chat-content";
    content.innerHTML = `
      <div class="chat-name">${c.name}</div>
      <div class="chat-message">${c.lastMessage}</div>
    `;

    // メタ情報（時間＋未読）
    const meta = document.createElement("div");
    meta.className = "chat-meta";
    meta.innerHTML = `
      <div>${c.time}</div>
      ${c.unread > 0 ? `<div class="unread">${c.unread}</div>` : ""}
    `;

    item.appendChild(avatar);
    item.appendChild(content);
    item.appendChild(meta);

    // クリック時の挙動（チャット画面に遷移する想定）
    item.addEventListener("click", () => {
      alert(c.name + " のチャットを開きます");
    });

    chatBox.appendChild(item);
  });
}

// 初期描画
renderConversations();
