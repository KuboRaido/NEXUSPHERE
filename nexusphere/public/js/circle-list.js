(() => {
  /* =========================
   * 1. HTMLエスケープ（XSS対策）
   * ========================= */
  const escapeHtml = (value = '') =>
    String(value).replace(/[&<>"']/g, (char) =>
      ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char])
    );

  /* =========================
   * 2. DOM取得
   * ========================= */
  const listRoot      = document.getElementById('circle-list');
  const searchInput   = document.getElementById('search-input');
  const searchResults = document.getElementById('search-results');

  const joinedRoot    = document.getElementById("circle-joined");
  const notJoinedRoot = document.getElementById("circle-not-joined");

  /* =========================
   * 3. URLテンプレート
   * ========================= */
  const linkTemplate =
    listRoot?.dataset.clubUrlTemplate || '/circle?id=__ID__';

  const resolveLink = (id) =>
    linkTemplate.replace('__ID__', encodeURIComponent(String(id)));

  /* =========================
   * 4. データ
   * ========================= */
  let circles = [];

  /* =========================
   * 5. サークル1件HTML生成
   * ========================= */
  const createCircleItem = (circle) => {
    const li = document.createElement('li');
    li.className = 'circle-item';

    const name    = circle.circle_name || circle.name || '';
    const icon    = circle.icon || window.DEFAULT_CLUB_ICON_URL || '';
    const members = circle.members_count ?? 0;

    li.innerHTML = `
      <a class="circle-link" href="${resolveLink(circle.circle_id ?? circle.id)}">
        <img src="${escapeHtml(icon)}" alt="${escapeHtml(name)}">
        <div class="circle-text">
          <span class="name">${escapeHtml(name)}</span>
          <span class="members">
            <i class="fa-solid fa-users"></i> ${members}人
          </span>
        </div>
      </a>
    `;
    return li;
  };

  /* =========================
   * 6. 一覧描画
   * ========================= */
  const renderList = (items) => {
    joinedRoot.innerHTML = '';
    notJoinedRoot.innerHTML = '';

    const joinedItems    = items.filter(c => c.joined === true);
    const notJoinedItems = items.filter(c => !c.joined);

    // 参加中
    if (!joinedItems.length) {
      joinedRoot.innerHTML = `<li class="empty">まだ参加しているサークルがありません</li>`;
    } else {
      joinedItems.forEach(c => joinedRoot.appendChild(createCircleItem(c)));
    }

    // 未参加
    if (!notJoinedItems.length) {
      notJoinedRoot.innerHTML = `<li class="empty">未参加のサークルはありません</li>`;
    } else {
      notJoinedItems.forEach(c => notJoinedRoot.appendChild(createCircleItem(c)));
    }
  };

  /* =========================
   * 7. 検索表示
   * ========================= */
  const showSearch = (items) => {
    searchResults.innerHTML = '';

    if (!items.length) {
      searchResults.innerHTML = `<li class="empty">サークルが見つかりませんでした</li>`;
      searchResults.style.display = 'block';
      return;
    }

    const fragment = document.createDocumentFragment();
    items.forEach(circle => fragment.appendChild(createCircleItem(circle)));

    searchResults.appendChild(fragment);
    searchResults.style.display = 'block';
  };

  const hideSearch = () => {
    searchResults.style.display = 'none';
    searchResults.innerHTML = '';
  };

  /* =========================
   * 8. 検索フィルタ
   * ========================= */
  const filterCircles = (keyword) => {
    const key = keyword.trim().toLowerCase();
    if (!key) return hideSearch();

    const matches = circles.filter(circle => {
      const name = (circle.circle_name || circle.name || '').toLowerCase();
      const category = (circle.category || '').toLowerCase();
      return name.includes(key) || category.includes(key);
    });

    showSearch(matches);
  };

  const debounce = (fn, delay = 300) => {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn(...args), delay);
    };
  };

  const debouncedFilter = debounce(filterCircles, 200);

  /* =========================
   * 9. API取得
   * ========================= */
  const fetchCircles = async () => {
    try {
      const res = await fetch('/api/v1/circle', {
        headers: { Accept: 'application/json' },
        credentials: 'include',
      });
      if (!res.ok) throw new Error();

      const body = await res.json();
      circles = Array.isArray(body) ? body : (body.data ?? []);
      renderList(circles);

    } catch (e) {
      console.error(e);
      joinedRoot.innerHTML =
        `<li class="error">サークル一覧の取得に失敗しました</li>`;
    }
  };

  /* =========================
   * 10. タブ切り替え
   * ========================= */
  document.addEventListener("DOMContentLoaded", () => {
    fetchCircles();

    document.querySelectorAll(".tab-btn").forEach(tab => {
      tab.addEventListener("click", () => {
        document.querySelectorAll(".tab-btn")
          .forEach(t => t.classList.remove("active"));
        tab.classList.add("active");

        const target = tab.dataset.target;
        joinedRoot.style.display    = target === "joined" ? "block" : "none";
        notJoinedRoot.style.display = target === "joined" ? "none"  : "block";
      });
    });

    if (searchInput) {
      searchInput.addEventListener('input', e => debouncedFilter(e.target.value));
      searchInput.addEventListener('focus', () => {
        if (searchInput.value.trim()) debouncedFilter(searchInput.value);
      });
      searchInput.addEventListener('blur', () => setTimeout(hideSearch, 200));
    }
  });

})();
