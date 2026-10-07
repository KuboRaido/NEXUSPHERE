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
    <div x-data="{ fruits: ['りんご'] }">
    <template x-for="(fruit, i) in fruits">
        <div>
            <input type="text" x-model="fruits[i]" :name="'fruits[' + i + ']'">
            <button type="button" @click="fruits.splice(i, 1)">行を削除</button>
        </div>
    </template>
    <button type="button" x-show="fruits.length < 5" @click="fruits.push('')">行を追加</button>
    </div>

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

                    {{-- 初期値に本名（$user->name）は入れない --}}
                    <label>
                        表示名（50文字まで）
                        <input type="text" name="display_name" maxlength="50"
                            value="{{ old('display_name', $site->display_name) }}" placeholder='例：田中太郎'>
                    </label>

                    <fieldset>
                        <legend>スキル(10個まで)</legend>
                        <div x-data="{ skills: @js(old('skills', $site->skills) ?: [['name' => '', 'detail' => '']]) }">
                            <template x-for="(skill, i) in skills">
                                <div>
                                    <input type="text" x-model="skill.name" :name="'skills[' + i + '][name]'" maxlength="30" placeholder='スキル名（例：PHP）'><br>
                                    <input type="text" x-model="skill.detail" :name="'skills[' + i + '][detail]'" maxlength="30" placeholder='詳細（例：3年）'>
                                    <button type="button" @click="skills.splice(i, 1)">行を削除</button>
                                </div>
                            </template>
                            <button type="button" x-show="skills.length < 10" @click="skills.push({ name: '', detail: '' })">行を追加</button>
                        </div>
                    </fieldset>

                    <label>
                        自己紹介（1000文字まで）
                        <textarea name="bio" rows="6" maxlength="1000">{{ old('bio', $site->bio) }}</textarea>
                    </label>

                    <fieldset>
                        <legend>リンク（5件まで。URLが空の行は保存されません）</legend>
                        @for ($i = 0; $i < 5; $i++)
                            <div class="link-row">
                                <input type="text" name="links[{{ $i }}][label]" placeholder="表示名（例：GitHub）"
                                    value="{{ old("links.$i.label", $site->links[$i]['label'] ?? '') }}">
                                <input type="url" name="links[{{ $i }}][url]" placeholder="https://"
                                    value="{{ old("links.$i.url", $site->links[$i]['url'] ?? '') }}">
                            </div>
                        @endfor
                    </fieldset>

                    <button type="submit">内容を保存する</button>
                </form>
            </section>
        @endif
    @endif
</main>
</body>
</html>