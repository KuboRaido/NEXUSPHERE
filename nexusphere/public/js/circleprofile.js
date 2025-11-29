document.addEventListener("DOMContentLoaded", function() {

  // ──────────── ログアウトダイアログ処理 ────────────
  const toggleBtn     = document.querySelector(".portfolio-toggle");
  const content       = document.querySelector(".portfolio-content");
  
  const trigger       = document.getElementById("logout-trigger");
  const confirmLogout = document.getElementById("logout-confirm");
  const yes           = document.getElementById("logout-yes");
  const no            = document.getElementById("logout-no");
  const form          = document.getElementById("logout-form");
  
  if (trigger && confirmLogout && yes && no && form) {
    const showConfirm = () => {
      confirmLogout.hidden = false;
      confirmLogout.style.display = 'grid';
    };
  
    const hideConfirm = () => {
      confirmLogout.hidden = true;
      confirmLogout.style.display = 'none';
    };
  
    hideConfirm();
  
    trigger.addEventListener('click', showConfirm);
    no.addEventListener('click', hideConfirm);
    yes.addEventListener('click', () => form.submit());
  
    confirmLogout.addEventListener('click', (event) => {
      if (event.target === event.currentTarget) hideConfirm();
    });
  
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && !confirmLogout.hidden) hideConfirm();
    });
  }

  // ──────────── ユーザー権限によるボタン出し分け ────────────
  const userRole = window.USER_ROLE || "owner"; // バックエンドでセットされる想定

  document.querySelectorAll('.role-owner, .role-member, .role-general')
    .forEach(el => el.style.display = "none");

  if (userRole === "owner") {
    document.querySelectorAll('.role-owner').forEach(el => el.style.display = "");
    document.querySelectorAll('.role-member').forEach(el => el.style.display = "");
  } 
  else if (userRole === "member") {
    document.querySelectorAll('.role-member').forEach(el => el.style.display = "");
  } 
  else if (userRole === "general") {
    document.querySelectorAll('.role-general').forEach(el => el.style.display = "");
  }

  // ──────────── 参加ボタン押下時（一般ユーザー用） ────────────
  document.querySelectorAll('.role-general').forEach(btn => {
    btn.addEventListener('click', function() {
      alert("参加申請を送信しました♡"); // デモ用
      btn.textContent = "参加申請済み";
      btn.disabled = true;
    });
  });
});
