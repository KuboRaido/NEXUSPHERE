<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>申請一覧</title>
<link rel="stylesheet" href="{{ asset('css/circleRequest.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<meta name="default-avatar" content="{{ asset('images/default-avatar.png') }}">
<script>window.DEFAULT_AVATAR_URL = "{{asset('images/default-avatar.png')}}"</script>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <a href="{{route('circle.profile', ['circle' => $circle->circle_id])}}" class="back-button">←</a>
            <h1 id="site-title">Nexusphere</h1>
        </div>
    </header>
    <div class=dmlist-container>
            <div class="search-container">

            </div>
            <ul class="request-list">
                @forelse($requests as $request)
                    <li class="request-item">
                        {{--アイコン--}}
                        <img src="{{ $request['user_icon'] ?? asset('images/default-avatar.png') }}" alt="icon" class="user-icon">

                        <div class="request-body">
                            {{--名前--}}
                            <span  class="request-name">{{ $request['user_name'] ?? "不明なユーザー"}} </span>

                        </div>
                        
                        @if($request['status'] === 'pending')

                            <div class="action-buttons">
                                {{--承認ボタン--}}
                                <form method="POST" action="{{route('circle.approve', ['circle' => $circle, 'circle_request' => $request['circle_request_id']])}}">
                                    @csrf
                                    <button type="submit" class="btn-approve">承認</button>
                                </form>

                                {{--拒否ボタン--}}
                                <form method="POST" action="{{route('circle.reject', ['circle' => $circle, 'circle_request' => $request['circle_request_id']])}}">
                                    @csrf
                                    <button type="submit" class="btn-reject">拒否</button>
                                </form>
                            </div>
                        @endif
                    </li>
                @empty
                    <li class="no-request">現在、参加申請はありません</li>
                @endforelse
            </ul>
    
<script src="{{ asset('js/circleRequest.js') }}"></script>
</body>
</html>
