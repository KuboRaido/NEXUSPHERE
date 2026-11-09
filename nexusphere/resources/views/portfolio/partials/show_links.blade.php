{{-- 公開ページのリンクのボタン（ヒーローとフッターの2か所で使う）。新しいタブで開き、noreferrer で token をリンク先に渡さない --}}
<ul class="portfolio__links">
    @foreach ($links as $link)
        <li>
            <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer">{{ ($link['label'] ?? '') !== '' ? $link['label'] : $link['url'] }}</a>
        </li>
    @endforeach
</ul>