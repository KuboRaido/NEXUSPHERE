(() => {
  // 1. HTMLエスケープ関数（XSS対策）
  const escapeHtml = (value = '') =>
    String(value).replace(/[&<>"']/g, (char) =>
      ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char] || char)
    );

  // 2. DOM取得
  const listRoot      = document.getElementById('circle-list');
  const searchInput   = document.getElementById('search-input');
  const searchResults = document.getElementById('search-results');

  // 3. URLテンプレート＆リンク生成関数
  const linkTemplate = listRoot.dataset.clubUrlTemplate || '/circle/__ID__';
  const resolveLink = (id) =>
    linkTemplate.replace('__ID__', encodeURIComponent(String(id)));

  // 4. サークル一覧データ
  let circles = [];

  // 5. 一覧描画
  const renderList = (items) => {
  const joinedRoot    = document.getElementById("circle-joined");
  const notJoinedRoot = document.getElementById("circle-not-joined");

  // 初期化
  joinedRoot.innerHTML = '';
  notJoinedRoot.innerHTML = '';
  if (listRoot) listRoot.innerHTML = '';

  // 参加・未参加で分ける
  const joinedItems    = items.filter(c => c.role === "member" || c.role === "owner");
  const notJoinedItems = items.filter(c => c.role === "guest");

  // ▼ 参加サークルが０件のとき
  if (joinedItems.length === 0) {
    joinedRoot.innerHTML = `
      <li class="empty">
        まだ参加しているサークルがありません
      </li>`;
  } else {
    // 通常の描画
    joinedItems.forEach((circle) => {
      const li = document.createElement('li');
      li.className = 'circle-item';

      const name = circle.circle_name || circle.name || '';
      const icon = circle.icon || window.DEFAULT_CLUB_ICON_URL || '';
      const sentence = circle.sentence || '';

      li.innerHTML = `
  <a class="circle-link" href="${resolveLink(circle.circle_id ?? circle.id)}">
    <img src="${escapeHtml(icon)}" alt="${escapeHtml(name)}">
    <span class="name">${escapeHtml(name)}</span>
    <span class="sentence">${escapeHtml(sentence)}</span>
  </a>
`;

      joinedRoot.appendChild(li);
    });
  }

  // ▼ 未参加サークルの描画
  if (notJoinedItems.length === 0) {
    notJoinedRoot.innerHTML = `
      <li class="empty">
        未参加のサークルはありません
      </li>`;
  } else {
    notJoinedItems.forEach((circle) => {
      const li = document.createElement('li');
      li.className = 'circle-item';

      const name = circle.circle_name || circle.name || '';
      const icon = circle.icon || window.DEFAULT_CLUB_ICON_URL || '';

      li.innerHTML = `
        <a class="circle-link" href="${resolveLink(circle.circle_id ?? circle.id)}">
          <img src="${escapeHtml(icon)}" alt="${escapeHtml(name)}">
          <span class="name">${escapeHtml(name)}</span>
        </a>
      `;
      notJoinedRoot.appendChild(li);
    });
  }
};



  // 6. 検索結果（丸く表示される修正版）
  const showSearch = (items) => {
    if (!items.length) {
      searchResults.innerHTML = '<li class="empty">サークルが見つかりませんでした</li>';
      searchResults.style.display = 'block';
      return;
    }

    const fragment = document.createDocumentFragment();
    items.forEach((circle) => {
      const li = document.createElement('li');
      li.className = 'circle-item';

      const name = circle.circle_name || circle.name || '';
      const icon = circle.icon || window.DEFAULT_CLUB_ICON_URL || '';
      const sentence = circle.sentence || '';

      li.innerHTML = `
        <a class="circle-link" href="${resolveLink(circle.circle_id ?? circle.id)}">
          <img src="${escapeHtml(icon)}" alt="${escapeHtml(name)}">
          <span class="name">${escapeHtml(name)}</span>
          <span class="sentence">${escapeHtml(sentence)}</span>
        </a>
      `;
      fragment.appendChild(li);
    });

    searchResults.replaceChildren(fragment);
    searchResults.style.display = 'block';
  };

  const hideSearch = () => {
    searchResults.style.display = 'none';
    searchResults.innerHTML = '';
  };

  // 7. 検索フィルタ
  const filterCircles = (keyword) => {
    const trimmed = keyword.trim();
    if (!trimmed) {
      hideSearch();
      return;
    }

    const lower = trimmed.toLowerCase();
    const matches = circles.filter((circle) => {
      const name     = (circle.circle_name || circle.name || '').toLowerCase();
      const category = (circle.category || '').toLowerCase();
      return name.includes(lower) || category.includes(lower);
    });

    showSearch(matches);
  };

  // 8. デバウンス
  const debounce = (fn, delay = 300) => {
    let timer = null;
    return (...args) => {
      if (timer) clearTimeout(timer);
      timer = setTimeout(() => fn(...args), delay);
    };
  };

  const debouncedFilter = debounce((value) => filterCircles(value), 200);

  // 9. API取得
  const fetchCircles = async () => {
    listRoot.innerHTML = '<li class="loading">読み込み中...</li>';

    try {
      const res = await fetch('/api/v1/circle', {
        headers: { Accept: 'application/json' },
        credentials: 'include',
      });
      if (!res.ok) throw new Error('サークル一覧の取得に失敗しました');

      const body = await res.json();
      circles = Array.isArray(body) ? body : (body.data ?? []);

      renderList(circles);
    } catch (error) {
      console.error(error);
      listRoot.innerHTML = '<li class="error">サークル一覧の取得に失敗しました</li>';
    }
  };

  // 10. イベント登録
  document.addEventListener('DOMContentLoaded', fetchCircles);

  if (searchInput) {
    searchInput.addEventListener('input', (event) => debouncedFilter(event.target.value));
    searchInput.addEventListener('focus', () => {
      if (searchInput.value.trim()) debouncedFilter(searchInput.value);
    });
    searchInput.addEventListener('blur', () => setTimeout(hideSearch, 200));
  }

    // ▼ タブ切り替え処理
  document.addEventListener("DOMContentLoaded", () => {
      const tabs = document.querySelectorAll(".tab-btn");
      const sections = {
          joined: document.getElementById("circle-joined"),
          notJoined: document.getElementById("circle-not-joined")
      };

      tabs.forEach(tab => {
          tab.addEventListener("click", () => {
              // タブの見た目を切り替え
              tabs.forEach(t => t.classList.remove("active"));
              tab.classList.add("active");

              const target = tab.dataset.target;

              // 表示切り替え
              if (target === "joined") {
                  sections.joined.style.display = "block";
                  sections.notJoined.style.display = "none";
              } else {
                  sections.joined.style.display = "none";
                  sections.notJoined.style.display = "block";
              }
          });
      });
  });

})();
