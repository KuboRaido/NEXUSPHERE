document.addEventListener("DOMContentLoaded", function() {
  const toggleBtn = document.querySelector(".portfolio-toggle");
  const content = document.querySelector(".portfolio-content");

  toggleBtn.addEventListener("click", () => {
    const isOpen = content.style.display === "block";
    content.style.display = isOpen ? "none" : "block";
    toggleBtn.innerHTML = isOpen 
      ? 'ポートフォリオを開く <i class="fa-solid fa-chevron-down"></i>' 
      : 'ポートフォリオを閉じる <i class="fa-solid fa-chevron-up"></i>';
  });
});
