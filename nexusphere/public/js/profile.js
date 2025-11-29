document.addEventListener("DOMContentLoaded", function() {
  const toggleBtn     = document.querySelector(".portfolio-toggle");
  const content       = document.querySelector(".portfolio-content");
  
  const trigger       = document.getElementById("logout-trigger");
  const confirmLogout = document.getElementById("logout-confirm");
  const yes           = document.getElementById("logout-yes");
  const no            = document.getElementById("logout-no");
  const form          = document.getElementById("logout-form");
  if (!trigger || !confirmLogout || !yes || !no || !form) return;

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
    if (event.target === event.currentTarget) {
      hideConfirm();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !confirmLogout.hidden) {
      hideConfirm();
    }
  });

  toggleBtn.addEventListener("click", () => {
    const isOpen = content.style.display === "block";
    content.style.display = isOpen ? "none" : "block";
    toggleBtn.innerHTML = isOpen 
      ? 'ポートフォリオ <i class="fa-solid fa-chevron-down"></i>' 
      : 'ポートフォリオ <i class="fa-solid fa-chevron-up"></i>';
  });
});
