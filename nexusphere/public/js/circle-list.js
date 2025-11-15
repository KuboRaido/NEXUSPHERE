(() => {
  const escapeHtml = (value = '') =>
    String(value).replace(/[&<>"']/g, (char) =>
      ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char] || char)
    );
  const listRoot = document.getElementById('circle-list');
  const searchInput = document.getElementById('search-input');
  const searchResults = document.getElementById('search-results');
  const linkTemplate = listRoot.dataset.chatUrlTemplate || '/circle?id=__ID__';
  let circles = [];
  const resolveLink = (id) => linkTemplate.replace('__ID__', encodeURIComponent(String(id)));
  const renderList = (items) => {
    if (!items.length) {
      listRoot.innerHTML = '<li class="empty">まだサークルがありません</li>';
      return;
    }
    const fragment = document.createDocumentFragment();
    items.forEach((circle) => {
      const li = document.createElement('li');
      li.className = 'club-item';
      li.innerHTML = `
        <a class="circle-link" href="${resolveLink(circle.circle_id ?? circle.id)}">
          <img class="club-icon" src="${escapeHtml(circle.icon)}">
          <div class="club-info">
            <div> 
              <span class="club-name">${escapeHtml(circle.circle_name || circle.name)}</span>
              <span class="circle-category">${escapeHtml(circle.category || '未分類')}</span>
              <span class="circle-members">${escapeHtml(String(circle.members_count ?? 0))}人</span>
              <div class="circle-sentence">${escapeHtml(circle.sentence)}</div>
            </div>
          </div>
        </a>
      `;
      fragment.appendChild(li);
    });
    listRoot.replaceChildren(fragment);
  };
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
      li.className = 'search-result-item';
      li.innerHTML = `
        <a class="circle-search-link" href="${resolveLink(circle.circle_id ?? circle.id)}">
          <img class="icon" src="${escapeHtml(circle.icon)}">
          <div class="search-content">
            <div class="search-name">${escapeHtml(circle.circle_name || circle.name || '名称未設定')}</div>
            <div class="search-meta">${escapeHtml(circle.category || '')}</div>
          </div>
        </a>
      `;
      fragment.appendChild(li);
    });
    searchResults.replaceChildren(fragment);
    searchResults.style.display = 'block';
  };
  const filterCircles = (keyword) => {
    const trimmed = keyword.trim();
    if (!trimmed) {
      hideSearch();
      return;
    }
    const lower = trimmed.toLowerCase();
    const matches = circles.filter((circle) => {
      const name = (circle.circle_name || circle.name || '').toLowerCase();
      const category = (circle.category || '').toLowerCase();
      return name.includes(lower) || category.includes(lower);
    });
    showSearch(matches);
  };
  const debounce = (fn, delay = 300) => {
    let timer = null;
    return (...args) => {
      if (timer) clearTimeout(timer);
      timer = setTimeout(() => fn(...args), delay);
    };
  };
  const debouncedFilter = debounce((value) => filterCircles(value), 200);
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