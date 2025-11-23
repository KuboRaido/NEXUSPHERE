document.addEventListener("DOMContentLoaded", function() {
  const toggleBtn     = document.querySelector(".portfolio-toggle");
  const content       = document.querySelector(".portfolio-content");
  
  const trigger       = document.getElementById("logout-trigger");
  const confirmLogout = document.getElementById("logout-confirm");
  const yes           = document.getElementById("logout-yes");
  const no            = document.getElementById("logout-no");
  const form          = document.getElementById("logout-form");

  if (!trigger || !confirmLogout || !yes || !no || !form) return;

  trigger.addEventListener('click', () => {
    confirmLayer.hidden = false;
  });

  no.addEventListener('click', () => {
    confirmLayer.hidden = true;
  });

  yes.addEventListener('click', () => {
    form.submit();
  });

  confirmLayer.addEventListener('click', (event) => {
    if (event.target === confirmLayer) confirmLayer.hidden = true;
  });

  toggleBtn.addEventListener("click", () => {
    const isOpen = content.style.display === "block";
    content.style.display = isOpen ? "none" : "block";
    toggleBtn.innerHTML = isOpen 
      ? 'ポートフォリオ <i class="fa-solid fa-chevron-down"></i>' 
      : 'ポートフォリオ <i class="fa-solid fa-chevron-up"></i>';
  });
});
