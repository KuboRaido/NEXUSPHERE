
  const fileInput = document.getElementById("image");
  const previewImg = document.getElementById("preview");

  fileInput.addEventListener("change", function () {
    const file = this.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (e) {
      previewImg.src = e.target.result;
    };
    reader.readAsDataURL(file);
  });

  document.addEventListener('DOMContentLoaded', () => {
  const typeSelect = document.getElementById('circle_type');
  const frequencySelect = document.getElementById('activity_frequency');

  if (!typeSelect || !frequencySelect) return;

  const frequencyData = {
    "ゆるく楽しむ系": ["不定期", "イベント時のみ","週1回", "週2回"],
    "本気でやる系": ["週1回","週2回", "週3回以上"],
    "勉強・研究系": ["週1回", "週2回", "週3回以上"],
    "イベント・告知系": ["不定期","イベント時のみ"]
  };

  typeSelect.addEventListener('change', function () {
    const selectedType = this.value;
    const frequencies = frequencyData[selectedType] || [];

    frequencySelect.innerHTML =
      '<option value="" disabled selected>活動頻度を選択してください</option>';

    if (frequencies.length > 0) {
      frequencies.forEach(freq => {
        const option = document.createElement('option');
        option.value = freq;
        option.textContent = freq;
        frequencySelect.appendChild(option);
      });
      frequencySelect.disabled = false;
    } else {
      frequencySelect.disabled = true;
    }
  });
});