@php
    /*
     * 公開ページ（案C-2）。決定は 04 決定ログ 2026-10-08「案C-2」、設計は 13 の10章。
     * セクションは「公開する」（isVisible）かつ中身があるときだけ出す。ナビにも同じ条件で出す。
     */
    $ym = fn (?string $p) => filled($p) ? substr($p, 0, 4) . '年' . (int) substr($p, 5, 2) . '月' : null;   // '2025-04' → 「2025年4月」

    $accent = in_array($site->accent_color, \App\Services\PortfolioSiteService::ACCENT_COLORS, true) ? $site->accent_color : 'blue';

    $name        = $site->isVisible('display_name') && filled($site->display_name) ? $site->display_name : null;
    $affiliation = collect(['school_name', 'department', 'major'])
        ->filter(fn ($key) => $site->isVisible($key) && filled($site->{$key}))
        ->map(fn ($key) => $site->{$key})
        ->implode('　');

    $showWorks   = $site->isVisible('works') && $works->isNotEmpty();
    $showStory   = $site->isVisible('life_story') && ! empty($site->life_story);
    $showAxis    = $site->isVisible('job_axis') && filled($site->job_axis);
    $showCareers = $site->isVisible('careers') && ! empty($site->careers);
    $showSkills  = $site->isVisible('skills') && ! empty($site->skills);
    $showCerts   = $site->isVisible('certifications') && ! empty($site->certifications);
    $showAwards  = $site->isVisible('awards') && ! empty($site->awards);
    $showHobbies = $site->isVisible('hobbies') && ! empty($site->hobbies);
    $customs     = collect($site->custom_sections ?? [])->filter(fn ($s) => ($s['visible'] ?? true) === true && filled($s['title'] ?? null));
    $showLinks   = $site->isVisible('links') && ! empty($site->links);

    $showSkillGroup = $showSkills || $showCerts || $showAwards;
    $showOthers     = $showHobbies || $customs->isNotEmpty();

    // 制作物の「詳しく見る」の6欄（中身があるものだけ出す）
    $detailLabels = [
        'team_role'      => 'チームと担当',
        'why_built'      => 'なぜ作ったか',
        'why_tech'       => 'なぜその技術を選んだか',
        'hardest_part'   => '技術的に苦労したところ',
        'own_ideas'      => '担当として工夫したこと',
        'current_status' => '現在どうなっているか',
    ];
@endphp

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex">
    {{-- 表示名を出さない設定なら、タブにも名前を出さない --}}
    <title>{{ $name ?? 'ポートフォリオ' }}</title>
    <link rel="stylesheet" href="{{ asset('css/portfolio_show.css') }}">
</head>
<body>
<div class="portfolio" data-accent="{{ $accent }}">
    <a class="portfolio__skip" href="#main">本文へ移動</a>

    <header class="portfolio__topbar">
        <div class="portfolio__inner">
            <a class="portfolio__brand" href="#top">{{ $name ?? 'ポートフォリオ' }}</a>
            <nav class="portfolio__nav" aria-label="ページ内の移動">
                <ul>
                    @if ($showWorks)      <li><a href="#works">制作物</a></li> @endif
                    @if ($showStory)      <li><a href="#story">人生の道筋</a></li> @endif
                    @if ($showAxis)       <li><a href="#axis">企業選びの軸</a></li> @endif
                    @if ($showCareers)    <li><a href="#careers">経歴と活動</a></li> @endif
                    @if ($showSkillGroup) <li><a href="#skills">スキル・資格・受賞歴</a></li> @endif
                    @if ($showOthers)     <li><a href="#others">その他</a></li> @endif
                </ul>
            </nav>
            @if ($showLinks)
                <a class="portfolio__contact-link" href="#contact">連絡先</a>
            @endif
        </div>
    </header>

    <section class="portfolio__hero" id="top">
        <div class="portfolio__inner">
            @auth
                @if (auth()->user()->user_id === $site->user_id)
                    <a class="portfolio__edit" href="{{ route('portfolio.edit') }}">設定を編集する</a>
                @endif
            @endauth

            @if ($affiliation !== '')
                <p class="portfolio__affiliation">{{ $affiliation }}</p>
            @endif
            @if ($name)
                <h1 class="portfolio__name">{{ $name }}</h1>
            @endif
            @if ($site->isVisible('bio') && filled($site->bio))
                <p class="portfolio__bio">{{ $site->bio }}</p>
            @endif
            @if ($showLinks)
                @include('portfolio.partials.show_links', ['links' => $site->links])
            @endif
        </div>
    </section>

    <main class="portfolio__main" id="main">

        @if ($showWorks)
            <section class="portfolio__section" id="works">
                <div class="portfolio__inner">
                    <div>
                        <h2 class="portfolio__heading">制作物</h2>
                    </div>
                    <div class="portfolio__works">
                        @foreach ($works as $work)
                            @php $first = $work->images->first(); @endphp
                            <article @class(['portfolio__work', 'portfolio__work--no-image' => ! $first])>
                                @if ($first)
                                    <div class="portfolio__work-image">
                                        {{-- 代表作の画像は最初の画面に近いので遅延読み込みにしない（表示が遅れて見えるのを防ぐ） --}}
                                        <img src="{{ Storage::disk('works')->url($first->path) }}" alt="{{ $work->title }}の画像"
                                            @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                                    </div>
                                @endif
                                @if ($loop->first)
                                    <p class="portfolio__featured-label">代表作</p>
                                @endif
                                <h3 class="portfolio__work-title">{{ $work->title }}</h3>
                                @if (filled($work->summary))
                                    <p class="portfolio__work-desc">{{ $work->summary }}</p>
                                @endif
                                @if (! empty($work->tech_stack))
                                    <ul class="portfolio__tags">
                                        @foreach ($work->tech_stack as $tech)
                                            <li>{{ $tech }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                                @if (filled($work->url))
                                    {{-- 新しいタブで開く。noreferrer で公開ページの URL（token）をリンク先に渡さない --}}
                                    <a class="portfolio__work-link" href="{{ $work->url }}" target="_blank" rel="noopener noreferrer">作品を見る</a>
                                @endif

                                @php $filled = collect($detailLabels)->filter(fn ($label, $key) => filled($work->{$key})); @endphp
                                @if ($filled->isNotEmpty() || $work->images->count() > 1)
                                    <details class="portfolio__work-more">
                                        <summary>詳しく見る</summary>
                                        @if ($filled->isNotEmpty())
                                            <dl class="portfolio__work-detail">
                                                @foreach ($filled as $key => $label)
                                                    <div><dt>{{ $label }}</dt><dd>{{ $work->{$key} }}</dd></div>
                                                @endforeach
                                            </dl>
                                        @endif
                                        @if ($work->images->count() > 1)
                                            <ul class="portfolio__gallery">
                                                @foreach ($work->images->skip(1) as $image)
                                                    <li><img src="{{ Storage::disk('works')->url($image->path) }}" alt="{{ $work->title }}の画像" loading="lazy"></li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </details>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if ($showStory)
            <section class="portfolio__section" id="story">
                <div class="portfolio__inner">
                    <div>
                        <h2 class="portfolio__heading">人生の道筋</h2>
                        <p class="portfolio__lead">今に至るまでの歩み</p>
                    </div>
                    <ol class="portfolio__story">
                        @foreach ($site->life_story as $row)
                            <li>
                                @if ($ym($row['period'] ?? null))
                                    <time datetime="{{ $row['period'] }}">{{ $ym($row['period']) }}</time>
                                @endif
                                <p>{{ $row['detail'] ?? '' }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>
        @endif

        @if ($showAxis)
            <section class="portfolio__section portfolio__section--band" id="axis">
                <div class="portfolio__inner">
                    <div>
                        <h2 class="portfolio__heading">企業選びの軸</h2>
                        <p class="portfolio__lead">会社を選ぶときに大事にしていること</p>
                    </div>
                    <p class="portfolio__text">{{ $site->job_axis }}</p>
                </div>
            </section>
        @endif

        @if ($showCareers)
            <section class="portfolio__section" id="careers">
                <div class="portfolio__inner">
                    <div>
                        <h2 class="portfolio__heading">経歴と活動</h2>
                    </div>
                    <ul class="portfolio__careers">
                        @foreach ($site->careers as $career)
                            <li>
                                @if ($ym($career['period'] ?? null))
                                    <time datetime="{{ $career['period'] }}">{{ $ym($career['period']) }}</time>
                                @else
                                    <span></span>
                                @endif
                                <p>{{ $career['content'] ?? '' }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif

        @if ($showSkillGroup)
            <section class="portfolio__section" id="skills">
                <div class="portfolio__inner">
                    <div>
                        <h2 class="portfolio__heading">スキル・資格・受賞歴</h2>
                    </div>
                    <div class="portfolio__columns">
                        @if ($showSkills)
                            <div>
                                <h3 class="portfolio__subheading">スキル</h3>
                                <ul class="portfolio__tags">
                                    @foreach ($site->skills as $skill)
                                        <li>{{ $skill['name'] ?? '' }}@if (filled($skill['detail'] ?? null))<small>{{ $skill['detail'] }}</small>@endif</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if ($showCerts)
                            <div>
                                <h3 class="portfolio__subheading">資格</h3>
                                <ul class="portfolio__items">
                                    @foreach ($site->certifications as $certification)
                                        <li>
                                            <p class="portfolio__item-name">{{ $certification['name'] ?? '' }}</p>
                                            @if ($ym($certification['acquired'] ?? null))
                                                <p class="portfolio__item-meta"><time datetime="{{ $certification['acquired'] }}">{{ $ym($certification['acquired']) }}</time></p>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if ($showAwards)
                            <div>
                                <h3 class="portfolio__subheading">受賞歴</h3>
                                <ul class="portfolio__items">
                                    @foreach ($site->awards as $award)
                                        <li>
                                            <p class="portfolio__item-name">{{ $award['name'] ?? '' }}</p>
                                            @if (filled($award['organizer'] ?? null) || $ym($award['period'] ?? null))
                                                <p class="portfolio__item-meta">
                                                    {{ $award['organizer'] ?? '' }}
                                                    @if ($ym($award['period'] ?? null))　<time datetime="{{ $award['period'] }}">{{ $ym($award['period']) }}</time>@endif
                                                </p>
                                            @endif
                                            @if (filled($award['description'] ?? null))
                                                <p class="portfolio__item-desc">{{ $award['description'] }}</p>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        @if ($showOthers)
            <section class="portfolio__section" id="others">
                <div class="portfolio__inner">
                    <div>
                        <h2 class="portfolio__heading">その他</h2>
                    </div>
                    <div class="portfolio__others">
                        @if ($showHobbies)
                            <div>
                                <h3 class="portfolio__subheading">趣味</h3>
                                <ul class="portfolio__tags">
                                    @foreach ($site->hobbies as $hobby)
                                        <li>{{ $hobby }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @foreach ($customs as $section)
                            <div>
                                <h3 class="portfolio__subheading">{{ $section['title'] }}</h3>
                                <p class="portfolio__text">{{ $section['body'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

    </main>

    <footer class="portfolio__footer" id="contact">
        <div class="portfolio__inner">
            @if ($showLinks)
                <h2 class="portfolio__footer-title">連絡先</h2>
                @include('portfolio.partials.show_links', ['links' => $site->links])
            @endif
            <p class="portfolio__credit">NEXUSPHERE で作成</p>
        </div>
    </footer>
</div>
</body>
</html>