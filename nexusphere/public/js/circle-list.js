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

    // ここでサークル一覧を描画
    listRoot.innerHTML = '';
    items.forEach(circle => {
      const li = document.createElement('li');
      li.className = 'circle-item';
      li.innerHTML = `
        <a href="${tpl.replace('__ID__', circle.circle_id)}">
          <img src="${circle.icon || window.DEFAULT_CLUB_ICON_URL}" alt="${circle.circle_name}">
          <div class="name">${circle.circle_name}</div>
        </a>
      `;
      listRoot.appendChild(li);
    });

  } catch(e) {
    console.error(e);
    listRoot.innerHTML = '<li class="error">サークル一覧の取得中にエラーが発生しました</li>';
  }
}
