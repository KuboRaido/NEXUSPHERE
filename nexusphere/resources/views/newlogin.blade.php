<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規登録</title>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/newlogin.css') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

</head>
<body>
    <div class="wrapper">
    <h1 class="title">Nexusphere</h1>
    <div class="form-container">
        <h1 class="header-text">新規登録</h1>
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ✅ 成功メッセージ表示 --}}
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

        <form id='register' method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
            @csrf
            <!-- メールアドレス -->
            <div>
                <label for="email" class="label-text">学校用のメールアドレス</label>
                <input type="email" id="mail" name="mail" placeholder="学校用のメールアドレスを入力" class="input-field" required>
            </div>

            <!-- パスワード -->
            <div>
                <label for="password" class="label-text">パスワード</label>
                <input type="password" id="password" name="password" placeholder="パスワードを入力" class="input-field" required>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="パスワードを再入力" class="input-field" required>
            </div>

            <!-- 名前 -->
            <div>
                <label for="name" class="label-text">本名</label>
                <input type="text" id="name" name="name" placeholder="本名を入力" class="input-field" required>
            </div>

            <!-- 年齢 -->
            <div>
                <label for="age" class="label-text">年齢</label>
                <input type="number" id="age" name="age" placeholder="年齢を入力" class="input-field" min="0" max="100" required>
            </div>

            <div>
                <label for="job" class="label-text">区分</label>
                <select id="job" name="job" class="input-field" required>
                    <option value="" disabled selected>区分を選択してください</option>
                    <option value="学生">学生</option>
                    <option value="講師">講師</option>
                    <option value="教員">教員</option>
                </select>
            </div>

            <!-- 学年 -->
            <div>
                <label for="grade" class="label-text">学年 </label>
                <select id="grade" name="grade" class="input-field" disabled>
                    <option value="" disabled selected>学年を選択してください</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                </select>
            </div>

            <!-- 学科 -->
            <div>
                <label for="subject" class="label-text">学科</label>
                <select id="subject" name="subject" class="input-field" disabled>
                    <option value="" disabled selected>区分を学生にしてください</option>
                    <option value="AI&テクノロジー科">AI&テクノロジー科</option>
                    <option value="デジタルテクノロジー科">デジタルテクノロジー科</option>
                    <option value="クリエイティブデザイン科">クリエイティブデザイン科</option>
                </select>
            </div>

            <!-- 専攻 -->
            <div>
                <label for="major" class="label-text">専攻</label>
                <select id="major" name="major" class="input-field" disabled>
                    <option value="" disabled selected>先に学科を選択してください</option>
                </select>
            </div>

            <!-- アイコン -->
            <div class="icon-upload-container">
                <label class="label-text">アイコン</label>
                <div class="icon-placeholder" id="iconPlaceholder">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16" width="40" height="40">
                        <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                        <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
                    </svg>
                    <img id="uploadedIcon" src="#" alt="Uploaded Icon" style="display: none;">
                </div>
                <input type="file" id="iconUpload" name="icon" accept="image/*" class="icon-upload-input">
            </div>          
        </form>
    </div>

        <!-- 登録ボタン --> 
        <div class="button-row">
            <button type="submit" class="submit-button" form="register">登録</button>
            <a href="{{ route('login') }}" class="cancel-button">戻る</a>
        </div>
    <!-- Custom JavaScript -->
    <script src="{{ asset('js/newlogin.js') }}"></script>
</body>
</html>
