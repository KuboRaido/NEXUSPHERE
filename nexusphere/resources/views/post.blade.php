<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .search-container{
            display:flex;
            justify-content: center;
            align-items: center;
            padding-top: 30px;

        }

        .search-form input[type="text"]{
            padding: 8px;
            font-size: 16px;
            width: 300px;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <div class="search-container">
        <form class="search-form" method="GET">
            <input type="text" name="keyword" placehoider="キーワードを入力">
        </form>
    </div>
</body>
</html>