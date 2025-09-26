// アイコンアップロードの処理
const iconPlaceholder = document.getElementById('iconPlaceholder');
const iconUploadInput = document.getElementById('iconUpload');
const uploadedIcon = document.getElementById('uploadedIcon');

iconPlaceholder.addEventListener('click', () => {
    iconUploadInput.click();
});

iconUploadInput.addEventListener('change', (event) => {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            uploadedIcon.src = e.target.result;
            uploadedIcon.style.display = 'block';
            iconPlaceholder.querySelector('svg').style.display = 'none'; // Hide SVG
        };
        reader.readAsDataURL(file);
    }
});
