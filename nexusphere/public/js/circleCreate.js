function previewImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('preview-image');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        preview.src = "";
    }
}

function previewImage(event) {
    const img = document.getElementById('preview-image');
    const plus = document.getElementById('plus');

    img.src = URL.createObjectURL(event.target.files[0]);
    img.style.display = 'block';
    plus.style.display = 'none';
}

