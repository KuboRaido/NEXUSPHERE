function joinCircle(button) {
  button.textContent = "参加済み";
  button.classList.add("joined");
  button.disabled = true;
}

function joinCircle(button) {
  button.textContent = "参加済み";
  button.classList.add("joined");
  button.disabled = true;
}

function filterCircles() {
  const input = document.getElementById("searchInput").value.toLowerCase();
  const items = document.getElementsByClassName("circle-item");

  for (let i = 0; i < items.length; i++) {
    const text = items[i].innerText.toLowerCase();
    if (text.includes(input)) {
      items[i].style.display = "";
    } else {
      items[i].style.display = "none";
    }
  }
}
