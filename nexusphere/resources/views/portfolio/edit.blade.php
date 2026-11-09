<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>公開サイトの設定</title>
    <link rel="stylesheet" href="{{ asset('css/portfolio_edit.css') }}">
    <script defer src="{{ asset('js/alpine-3.17.4.min.js') }}"></script>
</head>
<body>
<main class="portfolio-edit">
    <h1>公開サイトの設定</h1>

    {{-- 成功メッセージ（Controller の ->with('status', ...)） --}}
    @if (session('status'))
        <p class="message message--success">{{ session('status') }}</p>
    @endif

    {{-- エラー一覧（バリデーションと Service の DomainException） --}}
    @if ($errors->any())
        <ul class="message message--error">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    @if ($needsAgreement)
        {{-- ===== 未同意、または規約が改定されて再同意が必要なとき ===== --}}
        <section>
            @if ($site)
                <p>利用規約が改定されました。公開サイトを使い続けるには、新しい規約に同意してください。</p>
            @else
                <p>企業に渡せる自分専用のページを作れます。学内の情報（本名・学科など）は、自分で入力しない限り表示されません。</p>
            @endif

            <form method="POST" action="{{ route('portfolio.agree') }}">
                @csrf

                <fieldset>
                    <legend>作り方</legend>
                    <label>
                        <input type="radio" name="site_type" value="template"
                            @checked(old('site_type', $site?->site_type?->value ?? 'template') === 'template')>
                        テンプレートで作る
                    </label>
                    <label>
                        <input type="radio" name="site_type" value="external"
                            @checked(old('site_type', $site?->site_type?->value) === 'external')>
                        自分のポートフォリオのURLを登録する
                    </label>
                </fieldset>

                <label>
                    ポートフォリオのURL（URLを登録する場合のみ）
                    <input type="url" name="external_url" placeholder="https://"
                        value="{{ old('external_url', $site?->external_url) }}">
                </label>

                <label>
                    <input type="checkbox" name="agreed" value="1" @checked(old('agreed'))>
                    {{-- TODO: 規約のページができたら href を差し替える --}}
                    <a href="#" target="_blank" rel="noopener noreferrer">公開サイトの利用規約</a>に同意する
                </label>

                <button type="submit">同意して作成する</button>
            </form>
        </section>
    @else
        {{-- ===== 同意済みのとき ===== --}}

        {{-- 公開URLと公開状態 --}}
        <section>
            <h2>公開URL</h2>
            <p>
                @if ($site->site_type === \App\Enums\SiteType::External)
                    <a href="{{ $site->external_url }}" rel="noopener noreferrer">ポートフォリオを見る</a>
                @else
                    <a href="{{ route('portfolio.show', $site->token) }}" rel="noopener noreferrer">ポートフォリオを見る</a>
                @endif
            </p>

            @if ($site->is_public)
                <p>公開中です。このURLを知っている人だけが見られます。</p>
                <form method="POST" action="{{ route('portfolio.unpublish') }}">
                    @csrf
                    <button type="submit">公開を止める</button>
                </form>
            @else
                @if ($site->site_type === \App\Enums\SiteType::External)
                @else
                    <p>公開を止めています。URLを開いても表示されません。</p>
                    <form method="POST" action="{{ route('portfolio.publish') }}">
                        @csrf
                        <button type="submit">公開する</button>
                    </form>
                @endif
            @endif
        </section>

        {{-- 方式の切り替え（PUT） --}}
        <section>
            <h2>作り方</h2>
            <form method="POST" action="{{ route('portfolio.changeType') }}">
                @csrf
                @method('PUT')

                <label>
                    <input type="radio" name="site_type" value="template"
                        @checked(old('site_type', $site->site_type->value) === 'template')>
                    テンプレートで作る
                </label>
                <label>
                    <input type="radio" name="site_type" value="external"
                        @checked(old('site_type', $site->site_type->value) === 'external')>
                    自分のポートフォリオのURLを登録する
                </label>

                <label>
                    ポートフォリオのURL（URLを登録する場合のみ）
                    <input type="url" name="external_url" placeholder="https://"
                        value="{{ old('external_url', $site->external_url) }}">
                </label>

                <button type="submit">作り方を変更する</button>
            </form>
        </section>

        {{-- テンプレートの中身（テンプレート方式のときだけ） --}}
        @if ($site->site_type === \App\Enums\SiteType::Template)
            <section>
                <h2>ページの内容</h2>
                {{-- TODO: ルート名は routes/web.php のテンプレート保存のルートに合わせる --}}
                <form method="POST" action="{{ route('portfolio.updateTemplate') }}">
                    @csrf
                    @method('PUT')

                    {{-- 公開する：隠し入力欄（0）を前、チェックボックス（1）を後ろに置く。チェックが外れていても 0 が必ず届く --}}
                    <input type="hidden" name="visibility[display_name]" value="0">
                    <label><input type="checkbox" name="visibility[display_name]" value="1" @checked(filter_var(old('visibility.display_name', $site->visibility['display_name'] ?? true), FILTER_VALIDATE_BOOLEAN))>公開する</label>
                    <label>
                        表示名
                        <input type="text" name="display_name" maxlength="20"
                            value="{{ old('display_name', $site->display_name ?? $defaults['display_name'] ?? '' )}}" placeholder='例：田中太郎'>
                    </label>

                    <input type="hidden" name="visibility[school_name]" value="0">
                    <label><input type="checkbox" name="visibility[school_name]" value="1" @checked(filter_var(old('visibility.school_name', $site->visibility['school_name'] ?? true), FILTER_VALIDATE_BOOLEAN))>学校名を公開する</label>
                    <input type="hidden" name="visibility[department]" value="0">
                    <label><input type="checkbox" name="visibility[department]" value="1" @checked(filter_var(old('visibility.department', $site->visibility['department'] ?? true), FILTER_VALIDATE_BOOLEAN))>学科を公開する</label>
                    <input type="hidden" name="visibility[major]" value="0">
                    <label><input type="checkbox" name="visibility[major]" value="1" @checked(filter_var(old('visibility.major', $site->visibility['major'] ?? true), FILTER_VALIDATE_BOOLEAN))>専攻を公開する</label>
                    <label>
                        学校名・学科・専攻
                            <input type='text' name='school_name' maxlength='30' value="{{ old('school_name', $site->school_name)}}" placeholder="学校名を入力してください">
                            <input type='text' name='department' maxlength='20' value="{{ old('department', $site->department ?? $defaults['department'] ?? '')}}" placeholder="学科名を入力してください">
                            <input type='text' name='major' maxlength='20' value="{{ old('major', $site->major ??  $defaults['major'] ?? '')}}" placeholder="専攻名を入力してください">
                    </label>

                    <input type="hidden" name="visibility[bio]" value="0">
                    <label><input type="checkbox" name="visibility[bio]" value="1" @checked(filter_var(old('visibility.bio', $site->visibility['bio'] ?? true), FILTER_VALIDATE_BOOLEAN))>公開する</label>
                    <label>
                        自己紹介
                        <textarea name="bio" rows="6" maxlength="500" placeholder="500文字まで">{{ old('bio', $site->bio) }}</textarea>
                    </label>

                    <fieldset>
                        <legend>趣味(10個まで)</legend>
                        <input type="hidden" name="visibility[hobbies]" value="0">
                        <label><input type="checkbox" name="visibility[hobbies]" value="1" @checked(filter_var(old('visibility.hobbies', $site->visibility['hobbies'] ?? true), FILTER_VALIDATE_BOOLEAN))>公開する</label>
                        <div x-data="{ hobbies: @js(old('hobbies', $site->hobbies) ?: [''] )}">
                            <template x-for="(hobby, i) in hobbies">
                                <div>
                                    <input type="text" x-model="hobbies[i]" :name="'hobbies[' + i + ']'" maxlength="20" placeholder='趣味'>
                                    <button type="button" @click="hobbies.splice(i, 1)">行を削除</button>
                                </div>
                            </template>
                            <button type="button" x-show="hobbies.length < 10" @click="hobbies.push('')">行を追加</button>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>スキル(10個まで)</legend>
                        <input type="hidden" name="visibility[skills]" value="0">
                        <label><input type="checkbox" name="visibility[skills]" value="1" @checked(filter_var(old('visibility.skills', $site->visibility['skills'] ?? true), FILTER_VALIDATE_BOOLEAN))>公開する</label>
                        <div x-data="{ skills: @js(old('skills', $site->skills) ?: [['name' => '', 'detail' => '']]) }">
                            <template x-for="(skill, i) in skills">
                                <div>
                                    <input type="text" x-model="skill.name" :name="'skills[' + i + '][name]'" maxlength="30" placeholder='スキル名（例：PHP）'>
                                    <input type="text" x-model="skill.detail" :name="'skills[' + i + '][detail]'" maxlength="30" placeholder='詳細（例：3年）'>
                                    <button type="button" @click="skills.splice(i, 1)">行を削除</button>
                                </div>
                            </template>
                            <button type="button" x-show="skills.length < 10" @click="skills.push({ name: '', detail: '' })">行を追加</button>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>人生の道筋(10個まで)</legend>
                        <input type="hidden" name="visibility[life_story]" value="0">
                        <label><input type="checkbox" name="visibility[life_story]" value="1" @checked(filter_var(old('visibility.life_story', $site->visibility['life_story'] ?? true), FILTER_VALIDATE_BOOLEAN))>公開する</label>
                        <div x-data="{ life_story: @js(old('life_story', $ymLists['life_story'] ?? []) ?: [['year' => '', 'month' => '', 'detail' => '']]) }">
                            <template x-for="(life, i) in life_story">
                                <div>
                                    <select  x-model="life.year" :name="'life_story[' + i + '][year]'" >
                                        <option value="">年</option>
                                        @for ($y = 2010; $y <= now()->year + 5; $y++)
                                            <option value="{{ $y }}">{{ $y }}年</option>
                                        @endfor
                                    </select>
                                    <select x-model="life.month" :name="'life_story[' + i + '][month]'" >
                                        <option value="">月</option>
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ sprintf('%02d', $m) }}">{{ $m }}月</option>
                                        @endfor
                                    </select>
                                    <input type="text" x-model="life.detail" :name="'life_story[' + i + '][detail]'" maxlength="50" placeholder='詳細'>
                                    <button type="button" @click="life_story.splice(i, 1)">行を削除</button>
                                </div>
                            </template>
                            <button type="button" x-show="life_story.length < 10" @click="life_story.push({ year: '', month: '', detail: '' })">行を追加</button>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>資格(10個まで)</legend>
                        <input type="hidden" name="visibility[certifications]" value="0">
                        <label><input type="checkbox" name="visibility[certifications]" value="1" @checked(filter_var(old('visibility.certifications', $site->visibility['certifications'] ?? true), FILTER_VALIDATE_BOOLEAN))>公開する</label>
                        <div x-data="{ certifications: @js(old('certifications', $ymLists['certifications'] ?? []) ?: [['year' => '', 'month' => '', 'name' => '']]) }">
                            <template x-for="(certification, i) in certifications">
                                <div>
                                    <select  x-model="certification.year" :name="'certifications[' + i + '][year]'" >
                                        <option value="">年</option>
                                        @for ($y = 2010; $y <= now()->year + 5; $y++)
                                            <option value="{{ $y }}">{{ $y }}年</option>
                                        @endfor
                                    </select>
                                    <select x-model="certification.month" :name="'certifications[' + i + '][month]'" >
                                        <option value="">月</option>
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ sprintf('%02d', $m) }}">{{ $m }}月</option>
                                        @endfor
                                    </select>
                                    <input type="text" x-model="certification.name" :name="'certifications[' + i + '][name]'" maxlength="30" placeholder='資格名'>
                                    <button type="button" @click="certifications.splice(i, 1)">行を削除</button>
                                </div>
                            </template>
                            <button type="button" x-show="certifications.length < 10" @click="certifications.push({ year: '', month: '', name: '' })">行を追加</button>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>経歴と活動（20件まで）</legend>
                        <input type="hidden" name="visibility[careers]" value="0">
                        <label><input type="checkbox" name="visibility[careers]" value="1" @checked(filter_var(old('visibility.careers', $site->visibility['careers'] ?? true), FILTER_VALIDATE_BOOLEAN))>公開する</label>
                        <div x-data="{ careers: @js(old('careers', $ymLists['careers'] ?? []) ?: [['year' => '', 'month' => '', 'content' => '']]) }">
                            <template x-for="(career, i) in careers">
                                <div>
                                    <select x-model="career.year" :name="'careers[' + i + '][year]'">
                                        <option value="">年</option>
                                        @for ($y = 2010; $y <= now()->year + 5; $y++)
                                            <option value="{{ $y }}">{{ $y }}年</option>
                                        @endfor
                                    </select>
                                    <select x-model="career.month" :name="'careers[' + i + '][month]'">
                                        <option value="">月</option>
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ sprintf('%02d', $m) }}">{{ $m }}月</option>
                                        @endfor
                                    </select>
                                    <textarea x-model="career.content" :name="'careers[' + i + '][content]'" maxlength="500" rows="3" placeholder="内容（例：株式会社〇〇でECサイトの保守を担当）"></textarea>
                                    <button type="button" @click="careers.splice(i, 1)">行を削除</button>
                                </div>
                            </template>
                            <button type="button" x-show="careers.length < 20" @click="careers.push({ year: '', month: '', content: '' })">行を追加</button>
                        </div>
                    </fieldset>

                    <input type="hidden" name="visibility[job_axis]" value="0">
                    <label><input type="checkbox" name="visibility[job_axis]" value="1" @checked(filter_var(old('visibility.job_axis', $site->visibility['job_axis'] ?? true), FILTER_VALIDATE_BOOLEAN))>公開する</label>
                    <label>
                        就活軸
                        <textarea name='job_axis' maxlength='500' placeholder="就活の軸にしていることを入力してください">{{ old('job_axis', $site->job_axis)}}</textarea>
                    </label>
                    <fieldset>
                        <legend>受賞歴（20件まで）</legend>
                        <input type="hidden" name="visibility[awards]" value="0">
                        <label><input type="checkbox" name="visibility[awards]" value="1" @checked(filter_var(old('visibility.awards', $site->visibility['awards'] ?? true), FILTER_VALIDATE_BOOLEAN))>公開する</label>
                        <div x-data="{ awards: @js(old('awards', $ymLists['awards'] ?? []) ?: [['year' => '', 'month' => '', 'name' => '', 'organizer' => '', 'description' => '']]) }">
                            <template x-for="(award, i) in awards">
                                <div>
                                    <select x-model="award.year" :name="'awards[' + i + '][year]'">
                                        <option value="">年</option>
                                        @for ($y = 2010; $y <= now()->year + 5; $y++)
                                            <option value="{{ $y }}">{{ $y }}年</option>
                                        @endfor
                                    </select>
                                    <select x-model="award.month" :name="'awards[' + i + '][month]'">
                                        <option value="">月</option>
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ sprintf('%02d', $m) }}">{{ $m }}月</option>
                                        @endfor
                                    </select>
                                    <input type="text" x-model="award.name" :name="'awards[' + i + '][name]'" maxlength="20" placeholder="受賞名（例：最優秀賞）">
                                    <input type="text" x-model="award.organizer" :name="'awards[' + i + '][organizer]'" maxlength="30" placeholder="主催（例：校内ハッカソン2026）">
                                    <input type="text" x-model="award.description" :name="'awards[' + i + '][description]'" maxlength="50" placeholder="説明（例：3人チームで LINE Bot を作成）">
                                    <button type="button" @click="awards.splice(i, 1)">行を削除</button>
                                </div>
                            </template>
                            <button type="button" x-show="awards.length < 20" @click="awards.push({ year: '', month: '', name: '', organizer: '', description: '' })">行を追加</button>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>そのほか(10個まで)</legend>
                        <div x-data="{ custom_sections: @js($customSections) }">
                            <template x-for="(custom_section, i) in custom_sections">
                                <div>
                                    <input type="text" x-model="custom_section.title" :name="'custom_sections[' + i + '][title]'" maxlength="50" placeholder='項目名'>
                                    <textarea x-model="custom_section.body" :name="'custom_sections[' + i + '][body]'" maxlength="500" placeholder='詳細'></textarea>
                                    <label>
                                        <input type="checkbox" x-model="custom_section.visible" >この項目を公開する<br>
                                        <input type="hidden" :name="'custom_sections[' + i + '][visible]'" :value="custom_section.visible ? 1 : 0">
                                    </label>
                                    <button type="button" @click="custom_sections.splice(i, 1)">行を削除</button>
                                </div>
                            </template>
                            <button type="button" x-show="custom_sections.length < 10" @click="custom_sections.push({ title: '', body: '', visible: true })">行を追加</button>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>制作物</legend>
                        {{-- 制作物の中身は、このフォームの下の「制作物（1件ずつ保存）」で入力する。ここは公開ページに出すかだけ --}}
                        <input type="hidden" name="visibility[works]" value="0">
                        <label><input type="checkbox" name="visibility[works]" value="1" @checked(filter_var(old('visibility.works', $site->visibility['works'] ?? true), FILTER_VALIDATE_BOOLEAN))>制作物を公開する</label>
                    </fieldset>

                    <fieldset>
                        <legend>公開ページの色</legend>
                        @php $currentAccent = old('accent_color', $site->accent_color ?? 'blue'); @endphp
                        <div class="accent-picker">
                            @foreach (\App\Services\PortfolioSiteService::ACCENT_COLORS as $color)
                                <label class="accent-picker__item">
                                    <input type="radio" name="accent_color" value="{{ $color }}" @checked($currentAccent === $color)>
                                    <span class="accent-picker__swatch accent-picker__swatch--{{ $color }}"></span>
                                    {{ ['blue' => '青', 'teal' => '青緑', 'green' => '緑', 'amber' => '琥珀', 'coral' => '珊瑚', 'pink' => '桃', 'purple' => '紫', 'gray' => '灰'][$color] }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>リンク（5件まで。URLが空の行は保存されません）</legend>
                        <input type="hidden" name="visibility[links]" value="0">
                        <label><input type="checkbox" name="visibility[links]" value="1" @checked(filter_var(old('visibility.links', $site->visibility['links'] ?? true), FILTER_VALIDATE_BOOLEAN))>公開する</label>
                        @for ($i = 0; $i < 5; $i++)
                            <div class="link-row">
                                <input type="text" name="links[{{ $i }}][label]" maxlength="30" placeholder="表示名（例：GitHub）"
                                    value="{{ old("links.$i.label", $site->links[$i]['label'] ?? '') }}">
                                <input type="url" name="links[{{ $i }}][url]" placeholder="https://"
                                    value="{{ old("links.$i.url", $site->links[$i]['url'] ?? '') }}">
                            </div>
                        @endfor
                    </fieldset>

                    <button type="submit">内容を保存する</button>
                </form>
            </section>

            @include('portfolio.partials.works', ['works' => $works])
        @endif
    @endif
</main>
</body>
</html>