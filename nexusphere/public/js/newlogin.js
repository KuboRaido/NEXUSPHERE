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

//学科が選ばれたら対応する専攻だけを選ばせる処理
const majorData = {
    "AI&テクノロジー科" : [
        "AIエンジニア専攻",
    ]
};

document.addEventListener('DOMContentLoaded', () => {
    const subjectSelect = document.getElementById('subject');
    const majorSelect = document.getElementById('major');
    if(!subjectSelect || !majorSelect) return;

    //学科が選択||変更されたら
    subjectSelect.addEventListener('change',function(){
        const selectedSubject = this.value;
        const majors = majorData[selectedSubject] || [];

    majorSelect.innerHTML = '<option value="" disabled selected>専攻を選択してください</option>';
    
    if(majors.length > 0){
        //専攻の選択肢を追加していく
        majors.forEach(majorName => {
            const option = document.createElement('option');
            option.value = majorName;//裏で送信される値に専攻名をセット
            option.textContent = majorName;//画面に見える文字に専攻名をセット
            majorSelect.appendChild(option);//これで専攻プルダウンの中にこの作ったoptionを追加する
        });

        //選択可能にする
        majorSelect.disabled = false;
    } else {
        //選ばれた学科に対応する専攻がない場合は無効化のまま
        majorSelect.innerHTML = '<option value="" disabled selected>専攻をありません</option>';
        majorSelect.disabled = true;
    }
    });
});