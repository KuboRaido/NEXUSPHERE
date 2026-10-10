{{--
    制作物1件分の入力欄（新規と編集で共通）
    受け取るもの：
      $values   … 欄の値（old() か、保存済みの制作物の値。新規で初めて開いたときは空の配列）
      $images   … 枠の番号 => 保存済みの画像の行（新規は空）
      $idPrefix … label と入力欄を結ぶ id の頭（フォームごとに違う値にして、id が重ならないようにする）
    上限の数字は PortfolioWorkService::WORK_RULES と、マイグレーションの列の長さにそろえる。
--}}
@php
    $tech = array_values(array_filter((array) ($values['tech_stack'] ?? []), fn ($t) => ! blank($t)));
@endphp

<label>
    タイトル（50文字まで。ほかの欄に何か書いたら必須）
    <input type="text" name="title" maxlength="50" value="{{ $values['title'] ?? '' }}" placeholder="例：学内SNS">
</label>

<label>
    要約（100文字まで。カードに出ます）
    <input type="text" name="summary" maxlength="100" value="{{ $values['summary'] ?? '' }}" placeholder="例：学内の人を学年・学科で見つけ、外部SNSにつなぐサービス">
</label>

<fieldset>
    <legend>使った技術（10個まで、1つ30文字まで）</legend>
    <div x-data="{ tech: @js($tech ?: ['']) }">
        <template x-for="(t, i) in tech">
            <div class="tech-row">
                <input type="text" x-model="tech[i]" :name="'tech_stack[' + i + ']'" maxlength="30" placeholder="例：Laravel">
                <button type="button" class="button--secondary" @click="tech.splice(i, 1)">削除</button>
            </div>
        </template>
        <button type="button" class="button--secondary" x-show="tech.length < 10" @click="tech.push('')">技術を追加</button>
    </div>
</fieldset>

<label>
    作品のURL（https:// で始まるものだけ）
    <input type="url" name="url" maxlength="255" value="{{ $values['url'] ?? '' }}" placeholder="https://github.com/…">
</label>

<label>
    チームと担当（50文字まで）
    <input type="text" name="team_role" maxlength="50" value="{{ $values['team_role'] ?? '' }}" placeholder="例：4人チーム。リーダーとバックエンドを担当">
</label>

<label>
    なぜ作ったか（500文字まで）
    <textarea name="why_built" maxlength="500" rows="4" placeholder="例：学内で同じ技術に興味がある人を探す手段がなかった">{{ $values['why_built'] ?? '' }}</textarea>
</label>

<label>
    なぜその技術を選んだか（500文字まで）
    <textarea name="why_tech" maxlength="500" rows="4" placeholder="例：授業で学んだ Laravel なら、4人とも同じ書き方で分担できた">{{ $values['why_tech'] ?? '' }}</textarea>
</label>

<label>
    技術的に苦労したところ（500文字まで）
    <textarea name="hardest_part" maxlength="500" rows="4" placeholder="例：画像の保存と DB の保存がずれないように、保存の順番を設計した">{{ $values['hardest_part'] ?? '' }}</textarea>
</label>

<label>
    担当として工夫したこと（500文字まで）
    <textarea name="own_ideas" maxlength="500" rows="4" placeholder="例：機能を増やすのをやめ、外部SNSへの導線に絞った">{{ $values['own_ideas'] ?? '' }}</textarea>
</label>

<label>
    現在どうなっているか（500文字まで）
    <textarea name="current_status" maxlength="500" rows="4" placeholder="例：学内で限定公開中">{{ $values['current_status'] ?? '' }}</textarea>
</label>

<fieldset>
    <legend>画像（{{ \App\Services\PortfolioWorkService::IMAGES_MAX_COUNT }}枚まで。JPEG・PNG・WebP、1枚10MBまで。1枚目がカードに出ます）</legend>
    <p class="hint">選び直した枠は、保存したときに新しい画像に入れ替わります。画像は長い辺1600pxに縮め、位置情報を消してから保存します。</p>
    <div class="work-images">
        @for ($slot = 0; $slot < \App\Services\PortfolioWorkService::IMAGES_MAX_COUNT; $slot++)
            @php $image = $images->get($slot); @endphp
            <div class="work-image">
                <span class="work-image__label">{{ $slot + 1 }}枚目</span>
                @if ($image)
                    <img src="{{ Storage::disk('works')->url($image->path) }}" alt="{{ $slot + 1 }}枚目の画像" loading="lazy">
                    <label><input type="checkbox" name="delete_images[]" value="{{ $image->portfolio_work_image_id }}">この画像を消す</label>
                @endif
                <input type="file" name="new_images[{{ $slot }}]" accept="image/jpeg,image/png,image/webp" aria-label="{{ $slot + 1 }}枚目の画像を選ぶ">
            </div>
        @endfor
    </div>
</fieldset>
