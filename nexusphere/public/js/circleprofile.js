document.addEventListener("DOMContentLoaded", function() {
  // ──────────── ログアウトダイアログ処理 ────────────
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
  const userRole = window.USER_ROLE || "guest"; // バックエンドでセットされる想定

  document.querySelectorAll('.role-owner, .role-member, .role-guest')
    .forEach(el => el.style.display = "none");

  if (userRole === "owner") {
    document.querySelectorAll('.role-owner').forEach(el => el.style.display = "");
    document.querySelectorAll('.role-member').forEach(el => el.style.display = "");
  } 
  else if (userRole === "member") {
    document.querySelectorAll('.role-member').forEach(el => el.style.display = "");
  } 
  else if (userRole === "guest") {
    document.querySelectorAll('.role-guest').forEach(el => el.style.display = "");
  }

});
<<<<<<< HEAD

=======
>>>>>>> origin/main
document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("sidebar");
    const menuBtn = document.getElementById("menuBtn");
    const overlay = document.getElementById("overlay");

    menuBtn.addEventListener("click", function () {
        sidebar.classList.toggle("active");
        overlay.classList.toggle("active");
    });

    overlay.addEventListener("click", function () {
        sidebar.classList.remove("active");
        overlay.classList.remove("active");
    });
<<<<<<< HEAD
});
=======
});
>>>>>>> origin/main
