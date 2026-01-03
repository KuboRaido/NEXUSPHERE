(() => {
  'use strict';

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
  const listRoot        = document.getElementById('circle-list');
  const joinedRoot      = document.getElementById('circle-joined');
  const notJoinedRoot   = document.getElementById('circle-not-joined');
  const searchInput     = document.getElementById('search-input');
  const searchResults   = document.getElementById('search-results');

  // ★変更：必須DOMが無ければ即終了（JS全停止防止）
  if (!joinedRoot || !notJoinedRoot) return;

  /* =========================
   * 3. URL生成
   * ========================= */
  const linkTemplate =
    listRoot?.dataset.clubUrlTemplate || '/circle/__ID__'; // ★変更：optional chaining

  const resolveLink = (id) =>
    linkTemplate.replace('__ID__', encodeURIComponent(String(id)));

  /* =========================
   * 4. サークルデータ
   * ========================= */
  let circles = [];

  /* =========================
   * 5. 一覧描画（参加 / 未参加）
   * ========================= */
  const renderList = (items) => {
    joinedRoot.innerHTML = '';
    notJoinedRoot.innerHTML = '';

    const joinedItems    = items.filter(c => c.role === 'member' || c.role === 'owner');
    const notJoinedItems = items.filter(c => c.role === 'guest');

    /* ---- 参加サークル ---- */
    if (joinedItems.length === 0) {
      joinedRoot.innerHTML = `<li class="empty">まだ参加しているサークルがありません</li>`;
    } else {
      const fragment = document.createDocumentFragment(); // ★変更：fragment定義

      joinedItems.forEach(circle => {
        const li = document.createElement('li');
        li.className = 'circle-item';

        li.innerHTML = `
          <a class="circle-link" href="${resolveLink(circle.circle_id ?? circle.id)}">
            <img src="${escapeHtml(circle.icon || '')}">
            <span class="name">${escapeHtml(circle.circle_name || '')}</span>
            <span class="sentence">${escapeHtml(circle.sentence || '')}</span>
          </a>
        `;

        fragment.appendChild(li);
      });

      joinedRoot.replaceChildren(fragment);
    }

    /* ---- 未参加サークル ---- */
    if (notJoinedItems.length === 0) {
      notJoinedRoot.innerHTML = `<li class="empty">未参加のサークルはありません</li>`;
    } else {
      const fragment = document.createDocumentFragment(); // ★変更：fragment定義

      notJoinedItems.forEach(circle => {
        const li = document.createElement('li');
        li.className = 'circle-item';

        li.innerHTML = `
          <a class="circle-link" href="${resolveLink(circle.circle_id ?? circle.id)}">
            <img src="${escapeHtml(circle.icon || '')}">
            <div class="circle-text">
              <span class="name">${escapeHtml(circle.circle_name || '')}</span>
              <span class="sentence">${escapeHtml(circle.sentence || '')}</span>
            </div>
            <span class="members">👥 ${circle.member_count ?? 0}人</span>
          </a>
        `;

        fragment.appendChild(li);
      });

      notJoinedRoot.replaceChildren(fragment); // ★変更：listRoot → notJoinedRoot
    }
  };

  /* =========================
   * 6. 検索結果表示
   * ========================= */
  const showSearch = (items) => {
    searchResults.innerHTML = '';

    if (!items.length) {
      searchResults.innerHTML = `<li class="empty">サークルが見つかりませんでした</li>`;
      searchResults.style.display = 'block';
      return;
    }

    const fragment = document.createDocumentFragment(); // ★変更：fragment定義

    items.forEach(circle => {
      const li = document.createElement('li');
      li.className = 'circle-item';

      li.innerHTML = `
        <a class="circle-link" href="${resolveLink(circle.circle_id ?? circle.id)}">
          <img src="${escapeHtml(circle.icon || '')}">
          <span class="name">${escapeHtml(circle.circle_name || '')}</span>
          <span class="sentence">${escapeHtml(circle.sentence || '')}</span>
          <span class="members">👥 ${circle.member_count ?? 0}人</span>
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

  /* =========================
   * 7. 検索ロジック
   * ========================= */
  const filterCircles = (keyword) => {
    const key = keyword.trim().toLowerCase();
    if (!key) return hideSearch();

    const matches = circles.filter(c => {
      return (
        (c.circle_name || '').toLowerCase().includes(key) ||
        (c.category || '').toLowerCase().includes(key)
      );
    });

    showSearch(matches);
  };

  /* =========================
   * 8. debounce
   * ========================= */
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

      if (!res.ok) throw new Error('fetch failed');

      const body = await res.json();
      circles = Array.isArray(body) ? body : (body.data ?? []);

      renderList(circles);
    } catch (e) {
      console.error(e);
    }
  };

  /* =========================
   * 10. タブ切り替え
   * ========================= */
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tab-btn')
        .forEach(b => b.classList.remove('active'));

      btn.classList.add('active');

      const target = btn.dataset.target;
      joinedRoot.style.display    = target === 'joined' ? 'block' : 'none';
      notJoinedRoot.style.display = target === 'joined' ? 'none'  : 'block';
    });
  });

  /* =========================
   * 11. イベント登録
   * ========================= */
  document.addEventListener('DOMContentLoaded', fetchCircles);

  if (searchInput) {
    searchInput.addEventListener('input', e => debouncedFilter(e.target.value));
    searchInput.addEventListener('focus', () => {
      if (searchInput.value.trim()) debouncedFilter(searchInput.value);
    });
    searchInput.addEventListener('blur', () => setTimeout(hideSearch, 200));
  }
})();
