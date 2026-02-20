<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>サークルメンバー一覧</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/circlemember.css') }}">
</head>
<body>

<div class="container mt-5">
    <h1 class="mb-4 text-center">サークルメンバー一覧</h1>
        <a href="{{ url()->previous() }}" onclick="event.preventDefault(); history.back();" class="back-button">←</a>
    <!-- メンバー一覧 -->
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>名前</th>
                        <th>学年</th>
                        <th>役職</th>
                        <th>プロフィール</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                        <tr>
                            <td>{{ $member->name }}</td>
                            <td>{{ $member->grade }}年</td>
                            <td>{{ $member->job }}</td>
                            <td>
                                <a href="{{ route('profile', $member->user_id) }}" class="btn btn-sm btn-outline-primary">
                                    詳細を見る
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">
                                現在メンバー情報はありません
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>