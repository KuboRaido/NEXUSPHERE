// circle-list.js

document.addEventListener("DOMContentLoaded", async () => {
  const clubList = document.getElementById("club-list");
  const searchInput = document.getElementById("search-input");
  const searchResults = document.getElementById("search-results");
  const DEFAULT_ICON = window.DEFAULT_CLUB_ICON_URL || "/images/default-club.png";

  // --- 仮のサークルデータ（本来はAPIやDBから取得） ---
  const clubs = [
    { id: 1, name: "音楽研究会", description: "音楽を愛する仲間たち！", icon: null },
    { id: 2, name: "AI開発サークル", description: "AI技術で未来を創る！", icon: null },
    { id: 3, name: "バスケ部", description: "体育館で週3活動中！", icon: null },
    { id: 4, name: "写真愛好会", description: "カメラで世界を切り取ろう。", icon: null },
  ];

  // --- 一覧を描画する関数 ---
  function renderClubs(list) {
    clubList.innerHTML = "";
    const urlTemplate = clubList.dataset.clubUrlTemplate;

    list.forEach(club => {
      const li = document.createElement("li");
      li.classList.add("club-item");

      li.innerHTML = `
        <img src="${club.icon || DEFAULT_ICON}" alt="club icon" class="club-icon">
        <div class="club-info">
          <h3>${club.name}</h3>
          <p>${club.description}</p>
        </div>
      `;

      li.addEventListener("click", () => {
        const url = urlTemplate.replace("__ID__", club.id);
        window.location.href = url;
      });

      clubList.appendChild(li);
    });
  }

  // --- 初期表示 ---
  renderClubs(clubs);

  // --- 検索処理 ---
  searchInput.addEventListener("input", e => {
    const keyword = e.target.value.trim();
    if (keyword === "") {
      searchResults.style.display = "none";
      renderClubs(clubs);
      return;
    }

    const filtered = clubs.filter(c => c.name.includes(keyword) || c.description.includes(keyword));

    searchResults.innerHTML = "";
    filtered.forEach(c => {
      const li = document.createElement("li");
      li.textContent = c.name;
      li.classList.add("search-item");
      li.addEventListener("click", () => {
        searchInput.value = c.name;
        searchResults.style.display = "none";
        renderClubs([c]);
      });
      searchResults.appendChild(li);
    });

    searchResults.style.display = filtered.length > 0 ? "block" : "none";
  });
});
