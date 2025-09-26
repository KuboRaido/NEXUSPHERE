let messages = []; // ← メモリ内の仮メッセージリスト（DB接続前）

function sendMessage() {
    const currentUserId = document.getElementById('currentUserId').value;
    const recipientId = document.getElementById('recipientId').value;
    const input = document.getElementById('message-input');
    const text = input.value.trim();

    if (!text) return;

    const message = {
        id: Date.now(), // 一意なID（仮）
        from: currentUserId,
        to: recipientId,
        text: text,
        timestamp: new Date()
    };

    // DBに保存予定の場所
    /*
    fetch('/api/messages', {
        method: 'POST',
        body: JSON.stringify(message),
        headers: {
            'Content-Type': 'application/json'
        }
    }).then(response => response.json())
      .then(savedMessage => {
          messages.push(savedMessage);
          renderMessages();
      });
    */
    
    // 仮にメモリに保存
    messages.push(message);
    renderMessages();
    input.value = '';
}

function renderMessages() {
    const chatBox = document.getElementById('chat-box');
    const currentUserId = document.getElementById('currentUserId').value;

    chatBox.innerHTML = '';

    messages.forEach(msg => {
        const wrapper = document.createElement('div');
        wrapper.classList.add('message-wrapper');
        wrapper.classList.add(msg.from === currentUserId ? 'from-me' : 'from-them');

        const bubble = document.createElement('div');
        bubble.classList.add('message-bubble');
        bubble.textContent = msg.text;

        wrapper.appendChild(bubble);
        chatBox.appendChild(wrapper);
    });

    chatBox.scrollTop = chatBox.scrollHeight;
}


let lastEnterTime = 0;

document.getElementById("message-input").addEventListener("keydown", function (event) {
    if (event.key === "Enter") {
        const currentTime = new Date().getTime();

        if (currentTime - lastEnterTime < 500) {
            // 2回目のEnter（0.5秒以内）
            sendMessage();
            lastEnterTime = 0; // リセット
        } else {
            // 1回目のEnter
            lastEnterTime = currentTime;
        }
    }
});


// ページロード時にDBから読み込む予定の処理
/*
window.onload = () => {
    fetch(`/api/messages?user1=${currentUserId}&user2=${recipientId}`)
      .then(res => res.json())
      .then(data => {
          messages = data;
          renderMessages();
      });
};
*/
