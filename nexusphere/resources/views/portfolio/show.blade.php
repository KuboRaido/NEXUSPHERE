<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $site->display_name ?? 'ポートフォリオ' }}</title>
    <link rel="stylesheet" href="{{ asset('css/portfolio_show.css') }}">
</head>
<body>
<main class="portfolio">
    @auth
        @if (auth()->user()->user_id === $site->user_id)
            <a href="{{ route('portfolio.edit') }}">設定を編集する</a>
        @endif
    @endauth

    @if(filled($site->display_name))
        <h1 class="portfolio__name">{{ $site->display_name }}</h1>
    @endif
    @if(filled($site->bio))
        <p class="portfolio__bio">{{ $site->bio }}</p>
    @endif
    @if (! empty($site->links))
        <ul class="portfolio__links">
            @foreach ($site->links as $link)
                <li>
                    {{-- 新しいタブで開く。noreferrer で token をリンク先に渡さない --}}
                    <a class="portfolio__link" href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer">
                        {{ ($link['label'] ?? '') !== '' ? $link['label'] : $link['url'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</main>
</body>
</html>