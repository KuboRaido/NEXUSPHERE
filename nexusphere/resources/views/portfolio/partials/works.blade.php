{{--
    制作物（1件ずつ保存）
    受け取るもの：$works（この公開サイトの制作物。sort_order の順、images を一緒に読み込み済み）

    大きなフォーム（内容を保存する）の外に置く。制作物ごとに別のフォームで、1回の保存で1件だけ送る。
    どのフォームから送ったかを hidden の work_form（'new' か制作物の ID）で覚えておき、
    エラーで戻ったときは、そのフォームにだけ old() の値とエラーを出す（全部のフォームに同じ値が入らないように）。
--}}
@php
    $oldForm   = old('work_form');               // 直前に送ったフォーム（'new' か ID の文字列）。エラーで戻ったときだけ入っている
    $maxWorks  = \App\Services\PortfolioWorkService::WORKS_MAX_COUNT;
    $maxImages = \App\Services\PortfolioWorkService::IMAGES_MAX_COUNT;
@endphp

<section class="works" id="works"
    x-data="{ adding: @js($oldForm === 'new' || $works->isEmpty()) }">
    <h2>制作物（1件ずつ保存）</h2>
    <p>一番自信のある作品から登録してください。一番上（最初に登録した制作物）が、公開ページで「代表作」として大きく表示されます。</p>
    <p>制作物は1件ずつ「この制作物を保存」で保存します。保存していない入力は、別の制作物を保存したときに消えます。</p>

    {{-- ===== 保存済みの制作物 ===== --}}
    @foreach ($works as $work)
        @php
            $key    = (string) $work->portfolio_work_id;
            $useOld = $oldForm === $key;                       // このフォームから送ってエラーで戻ってきたか
            $images = $work->images->keyBy('sort_order');      // 枠の番号 => 画像の行
        @endphp

        <article class="work-form">
            <h3>{{ $loop->first ? '制作物 1（代表作）' : '制作物 ' . $loop->iteration }}</h3>

            @if ($useOld && $errors->any())
                <ul class="message message--error">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <form method="POST" action="{{ route('portfolio.works.update', $work->portfolio_work_id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="work_form" value="{{ $key }}">

                @include('portfolio.partials.work-fields', [
                    'values' => $useOld ? old() : $work->only(['title', 'summary', 'tech_stack', 'url', 'team_role', 'why_built', 'why_tech', 'hardest_part', 'own_ideas', 'current_status']),
                    'images' => $images,
                    'idPrefix' => 'work-' . $key,
                ])

                <button type="submit">この制作物を保存</button>
            </form>

            {{-- 削除は別のフォーム（フォームの中にフォームは置けない）。押したら確認を出す。
                 タイトルは学生が書いた文字なので、JavaScript の文字列には Js::from で入れる（{{ }} だけだと ' で文字列が切れ、好きな JS を動かせてしまう） --}}
            <form method="POST" action="{{ route('portfolio.works.destroy', $work->portfolio_work_id) }}"
                @submit="if (!confirm({{ Js::from('「' . $work->title . '」を削除します。画像も消え、元に戻せません。よろしいですか？') }})) $event.preventDefault()">
                @csrf
                @method('DELETE')
                <button type="submit" class="button--danger">この制作物を削除</button>
            </form>
        </article>
    @endforeach

    {{-- ===== 新しい制作物（1件ずつ） ===== --}}
    @if ($works->count() < $maxWorks)
        <button type="button" x-show="!adding" @click="adding = true">制作物を追加</button>

        <article class="work-form" x-show="adding" x-cloak>
            <h3>新しい制作物</h3>

            @if ($oldForm === 'new' && $errors->any())
                <ul class="message message--error">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <form method="POST" action="{{ route('portfolio.works.store') }}" enctype="multipart/form-data" x-ref="newForm">
                @csrf
                <input type="hidden" name="work_form" value="new">

                @include('portfolio.partials.work-fields', [
                    'values' => $oldForm === 'new' ? old() : [],
                    'images' => collect(),
                    'idPrefix' => 'work-new',
                ])

                <button type="submit">この制作物を保存</button>
                {{-- 未保存の行は画面から消すだけ（DB には何も無い） --}}
                <button type="button" class="button--secondary" @click="$refs.newForm.reset(); adding = false">入力をやめる</button>
            </form>
        </article>
    @else
        <p>制作物は{{ $maxWorks }}件まで登録できます。新しく登録するには、どれかを削除してください。</p>
    @endif
</section>
