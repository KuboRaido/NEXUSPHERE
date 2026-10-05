<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">
    <title>{{ $site->display_name ?? 'ポートフォリオ' }}</title>
</head>
<body>
    <h1>{{ $site->display_name }}</h1>
    <p>{{ $site->bio }}</p>
</body>
</html>