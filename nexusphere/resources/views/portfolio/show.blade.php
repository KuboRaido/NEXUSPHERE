<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    {{-- 表示名を出さない設定なら、タブにも名前を出さない --}}
    <title>{{ $site->isVisible('display_name') && filled($site->display_name) ? $site->display_name : 'ポートフォリオ' }}</title>
    <link rel="stylesheet" href="{{ asset('css/portfolio_show.css') }}">
</head>
<body>
<main class="portfolio">
    @auth
        @if (auth()->user()->user_id === $site->user_id)
            <a class="portfolio__edit" href="{{ route('portfolio.edit') }}">設定を編集する</a>
        @endif
    @endauth

    {{-- どの項目も「公開する」（isVisible）かつ中身があるときだけ出す --}}
    @if ($site->isVisible('display_name') && filled($site->display_name))
        <h1 class="portfolio__name">{{ $site->display_name }}</h1>
    @endif

    @if ($site->isVisible('school_name') && filled($site->school_name))
        <p class="portfolio__school-name">{{ $site->school_name }}</p>
    @endif
    @if ($site->isVisible('department') && filled($site->department))
        <p class="portfolio__department">{{ $site->department }}</p>
    @endif
    @if ($site->isVisible('major') && filled($site->major))
        <p class="portfolio__major">{{ $site->major }}</p>
    @endif

    @if ($site->isVisible('bio') && filled($site->bio))
        <p class="portfolio__bio">{{ $site->bio }}</p>
    @endif

    @if ($site->isVisible('job_axis') && filled($site->job_axis))
        <section class="portfolio__section">
            <h2>企業選びの軸</h2>
            <p class="portfolio__job-axis">{{ $site->job_axis }}</p>
        </section>
    @endif

    @if ($site->isVisible('hobbies') && ! empty($site->hobbies))
        <section class="portfolio__section">
            <h2>趣味</h2>
            <ul class="portfolio__hobbies">
                @foreach ($site->hobbies as $hobby)
                    <li>{{ $hobby }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    @if ($site->isVisible('life_story') && ! empty($site->life_story))
        <section class="portfolio__section">
            <h2>人生の道筋</h2>
            <ul class="portfolio__life-story">
                @foreach ($site->life_story as $row)
                    <li>
                        {{-- '2025-04' を「2025年4月」にする --}}
                        @if (filled($row['period'] ?? null))
                            <span>{{ substr($row['period'], 0, 4) }}年{{ (int) substr($row['period'], 5, 2) }}月</span>
                        @endif
                        {{ $row['detail'] ?? '' }}
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if ($site->isVisible('skills') && ! empty($site->skills))
        <section class="portfolio__section">
            <h2>スキル</h2>
            <ul class="portfolio__skills">
                @foreach ($site->skills as $skill)
                    <li>
                        {{ $skill['name'] ?? '' }}
                        @if (filled($skill['detail'] ?? null))
                            <span>（{{ $skill['detail'] }}）</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if ($site->isVisible('certifications') && ! empty($site->certifications))
        <section class="portfolio__section">
            <h2>資格</h2>
            <ul class="portfolio__certifications">
                @foreach ($site->certifications as $certification)
                    <li>
                        @if (filled($certification['acquired'] ?? null))
                            <span>{{ substr($certification['acquired'], 0, 4) }}年{{ (int) substr($certification['acquired'], 5, 2) }}月</span>
                        @endif
                        {{ $certification['name'] ?? '' }}
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if ($site->isVisible('careers') && ! empty($site->careers))
        <section class="portfolio__section">
            <h2>経歴と活動</h2>
            <ul class="portfolio__careers">
                @foreach ($site->careers as $career)
                    <li>
                        @if (filled($career['period'] ?? null))
                            <span>{{ substr($career['period'], 0, 4) }}年{{ (int) substr($career['period'], 5, 2) }}月</span>
                        @endif
                        {{ $career['content'] ?? '' }}
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if ($site->isVisible('awards') && ! empty($site->awards))
        <section class="portfolio__section">
            <h2>受賞歴</h2>
            <ul class="portfolio__awards">
                @foreach ($site->awards as $award)
                    <li>
                        @if (filled($award['period'] ?? null))
                            <span>{{ substr($award['period'], 0, 4) }}年{{ (int) substr($award['period'], 5, 2) }}月</span>
                        @endif
                        {{ $award['name'] ?? '' }}
                        @if (filled($award['organizer'] ?? null))
                            <span>（{{ $award['organizer'] }}）</span>
                        @endif
                        @if (filled($award['description'] ?? null))
                            <p>{{ $award['description'] }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- 追加項目は行ごとの visible で出す・出さないを決める --}}
    @foreach ($site->custom_sections ?? [] as $section)
        @if (($section['visible'] ?? true) === true)
            <section class="portfolio__section">
                <h2>{{ $section['title'] ?? '' }}</h2>
                <p>{{ $section['body'] ?? '' }}</p>
            </section>
        @endif
    @endforeach

    @if ($site->isVisible('links') && ! empty($site->links))
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