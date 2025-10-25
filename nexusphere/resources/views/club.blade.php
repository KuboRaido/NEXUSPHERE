<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>サークル一覧</title>
<link rel="stylesheet" href="{{ asset('css/club.css') }}">
</head>
<body>

 <div class="search-container">
    <input type="text" id="searchInput" placeholder="サークル名やカテゴリで検索..." onkeyup="filterCircles()">
  </div>

  <div class="circle-list">
    <div class="circle-item">
      <div class="circle-info">
        <div class="circle-icon">
          <img src="https://via.placeholder.com/50" alt="アイコン">
        </div>
        <div class="circle-text">
          <h3>コーヒー同好会</h3>
          <p>カテゴリ: 趣味</p>
          <p>メンバー数: 124人</p>
        </div>
      </div>
      <button class="join-btn" onclick="joinCircle(this)">参加する</button>
    </div>

    <div class="circle-item">
      <div class="circle-info">
        <div class="circle-icon">
          <img src="https://via.placeholder.com/50" alt="アイコン">
        </div>
        <div class="circle-text">
          <h3>フットサルチーム</h3>
          <p>カテゴリ: スポーツ</p>
          <p>メンバー数: 56人</p>
        </div>
      </div>
      <button class="join-btn joined" disabled>参加済み</button>
    </div>
  </div>

 <script src="{{ asset('js/club.js') }}"></script>
</html>
