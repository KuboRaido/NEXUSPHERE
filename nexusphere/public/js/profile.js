document.addEventListener("DOMContentLoaded", function() {
    const toggleBtn = document.querySelector(".portfolio-toggle");
    const content   = document.querySelector(".portfolio-content");

    const trigger       = document.getElementById("logout-trigger");
    const confirmLogout = document.getElementById("logout-confirm");
    const yes           = document.getElementById("logout-yes");
    const no            = document.getElementById("logout-no");
    const form          = document.getElementById("logout-form");

    if (trigger && confirmLogout && yes && no && form) {
    const showConfirm = () => {
        confirmLogout.hidden = false;
        confirmLogout.style.display = "grid";
    };

    const hideConfirm = () => {
        confirmLogout.hidden = true;
        confirmLogout.style.display = "none";
    };

    hideConfirm();

    trigger.addEventListener("click", showConfirm);
    no.addEventListener("click", hideConfirm);
    yes.addEventListener("click", () => form.submit());

    confirmLogout.addEventListener("click", (e) => {
        if (e.target === e.currentTarget) hideConfirm();
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && !confirmLogout.hidden) hideConfirm();
    });
    }

  // ▼ ポートフォリオ開閉（ここが重要）
    toggleBtn.addEventListener("click", () => {
    content.classList.toggle("is-open");

    toggleBtn.innerHTML = content.classList.contains("is-open")
        ? '<i class="fa-solid fa-chevron-up"></i>'
        : '<i class="fa-solid fa-chevron-down"></i>';
    });
});
