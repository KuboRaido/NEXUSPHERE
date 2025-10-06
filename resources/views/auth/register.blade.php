<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規登録 - Nexusphere</title>
    <style>
        body {
            font-family: sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1.title {
            font-family: "Orbitron", sans-serif;
            text-align: center;
            font-size: 2rem;
            margin: 40px 0 10px;
            color: #333;
        }

        .register-box {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 400px;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            font-size: 0.95rem;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
        }

        button {
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            background-color: #007bff;
            border: none;
            color: white;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* ハンバーガーメニュー */
        .menu-btn {
            position: fixed;
            top: 15px;
            right: 15px;
            display: flex;
            height: 50px;
            width: 50px;
            justify-content: center;
            align-items: center;
            z-index: 90;
            background-color: #3584bb;
            border-radius: 8px;
        }
        .menu-btn span,
        .menu-btn span:before,
        .menu-btn span:after {
            content: '';
            display: block;
            height: 3px;
            width: 25px;
            border-radius: 3px;
            background-color: #ffffff;
            position: absolute;
        }
        .menu-btn span:before {
            bottom: 8px;
        }
        .menu-btn span:after {
            top: 8px;
        }
        #menu-btn-check {
            display: none;
        }

        /* スマホ向け */
        @media (max-width: 480px) {
            h1.title {
                font-size: 1.6rem;
                margin: 20px 0 10px;
            }
            .register-box {
                padding: 20px;
                max-width: 95%;
            }
            label {
                font-size: 0.85rem;
            }
            input, button {
                font-size: 0.9rem;
                padding: 10px;
            }
            .menu-btn {
                height: 40px;
                width: 40px;
            }
            .menu-btn span,
            .menu-btn span:before,
            .menu-btn span:after {
                width: 20px;
            }
        }

        /* タブレット向け */
        @media (min-width: 481px) and (max-width: 768px) {
            h1.title {
                font-size: 1.8rem;
            }
            .register-box {
                padding: 25px;
                max-width: 80%;
            }
            input, button {
                font-size: 1rem;
            }
        }

        /* PC向け */
        @media (min-width: 769px) {
            h1.title {
                font-size: 2.2rem;
            }
            .register-box {
                max-width: 450px;
            }
        }
    </style>
</head>
<body>
    <div class="flex">
      <h1 class="title">Nexusphere</h1>
      <div class="hamburger-menu">
        <input type="checkbox" id="menu-btn-check">
        <label for="menu-btn-check" class="menu-btn"><span></span></label>
      </div>
    </div>

    <div class="register-box">
        <h2>新規登録</h2>
        <form action="/register" method="POST" enctype="multipart/form-data">
            @csrf

            <label>名前</label>
            <input type="text" name="name" required>

            <label>メールアドレス</label>
            <input type="email" name="email" required>

            <label>パスワード</label>
            <input type="password" name="password" required>

            <label>年齢</label>
            <input type="number" name="age">

            <label>学年</label>
            <input type="text" name="grade">

            <label>学科</label>
            <input type="text" name="department">

            <label>専攻</label>
            <input type="text" name="major">

            <label>アイコン画像</label>
            <input type="file" name="icon">

            <button type="submit">登録する</button>
        </form>
    </div>
</body>
</html>
