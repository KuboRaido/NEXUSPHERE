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
  const linkTemplate = listRoot.dataset.chatUrlTemplate || '/circle?id=__ID__';
  const resolveLink = (id) =>
    linkTemplate.replace('__ID__', encodeURIComponent(String(id)));

  // 4. サークル一覧データ
  let circles = [];

  // 5. 一覧描画
  const renderList = (items) => {
    if (!items.length) {
      listRoot.innerHTML = '<li class="empty">まだサークルがありません</li>';
      return;
    }

    const fragment = document.createDocumentFragment();
    items.forEach((circle) => {
      const li = document.createElement('li');
      li.className = 'circle-item';
      const name = circle.circle_name || circle.name || '';
      const icon = circle.icon || window.DEFAULT_CLUB_ICON_URL || '';

      li.innerHTML = `
        <a href="${resolveLink(circle.circle_id ?? circle.id)}">
          <img src="${escapeHtml(icon)}" alt="${escapeHtml(name)}">
          <div class="name">${escapeHtml(name)}</div>
        </a>
      `;
      fragment.appendChild(li);
    });

    listRoot.replaceChildren(fragment);
  };

  // 6. 検索結果の表示/非表示
  const hideSearch = () => {
    if (!searchResults) return;
    searchResults.style.display = 'none';
    searchResults.innerHTML = '';
  };

  const showSearch = (items) => {
    if (!searchResults) return;

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

      li.innerHTML = `
        <a href="${resolveLink(circle.circle_id ?? circle.id)}">
          <img src="${escapeHtml(icon)}" alt="${escapeHtml(name)}">
          <div class="name">${escapeHtml(name)}</div>
        </a>
      `;
      fragment.appendChild(li);
    });

    searchResults.replaceChildren(fragment);
    searchResults.style.display = 'block';
  };

  // 7. フィルタ（検索）
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

  // 9. APIから取得
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
    searchInput.addEventListener('blur', () => {
      setTimeout(hideSearch, 200);
    });
  }
})();
