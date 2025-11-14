//Circle検索
let searchTimeout = null;

async function searchUsers(keyword){
  const searchResults = document.getElementById('search-results');

  if(!keyword || keyword.trim() === ''){
    searchResults.innerHTML = '';
    searchResults.style.display = 'none';
    return;
  }

  try{
    const res = await fetch(`/api/v1/circle/search?q=${encodeURIComponent(keyword)}`,{
      headers: {'Accept': 'application/json'},
      credentials: 'include',
    });

    if(!res.ok) throw new Error('検索に失敗しました');

    const circles = await res.json();

    if(circles.length === 0){
    searchResults.innerHTML = '<li class="empty">サークルが見つかりませんでした</li>';
    searchResults.style.display = 'block';
    return;
  }

  // 検索結果を表示
  searchResults.innerHTML = '';
  searchResults.style.display = 'block';

  for(const circle of circles){
    const li = document.createElement('li');
    li.className = 'search-result-item';
    li.innerHTML = `
    <a class ="circle-id" href="/dm?to=${circle.circle_id}">
        <img class="icon" src="${circle.icon}" alt="" ">
        <div class="search-content">
         <div class="search-name">${escapeHtml(circle.circle_name)}</div>
        </div>
    </a>
    `;
    searchResults.appendChild(li);
  }
  } catch(e) {
    console.error(e);
    searchResults.innerHTML = '<li class="error">検索中にエラーが発生しました</li>';
    searchResults.style.display = 'block';
  }
}

async function circlelist(){
  const listRoot = document.getElementById('circle-list');
  
  listRoot.innerHTML = '<li class="loading">読み込み中...</li>';

  try{
    const res = await fetch('api/v1/circle',{
      headers: {'Accept': 'application/json'},
      credentials: 'include',
    });
    const json = await res.json();
    const items = Array.isArray(json) ? json : (json.data ?? json.dms ?? []);
    const tpl = listRoot.dataset.chatUrlTemplate || '/dm?to=__ID__';

    
  }
}