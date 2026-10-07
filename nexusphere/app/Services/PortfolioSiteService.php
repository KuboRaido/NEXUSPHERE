<?php

namespace App\Services;

use App\Enums\SiteType;
use App\Models\PortfolioSite;
use App\Models\User;
use DomainException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// ポートフォリオの作成に必要なルールを守るための処理
class PortfolioSiteService
{
    private const TOKEN_LENGTH = 16;
    private const EXTERNAL_URL_MAX_LENGTH = 255;
    private const LINK_LABEL_MAX_LENGTH = 30;

    /**
     * 規約に同意して公開サイトを作る（既にあれば再同意として更新する）
     */
    public function agreeAndCreate(User $user, SiteType $siteType, ?string $externalUrl = null): PortfolioSite
    {
        return DB::transaction(function () use ($user, $siteType, $externalUrl) {
            $site = PortfolioSite::firstOrNew(['user_id' => $user->user_id]);

            if (! $site->token) {
                $site->token = $this->generateToken();
            }

            $site->agreed_at     = now();
            $site->terms_version = config('terms.current_version');

            $isNewSite = ! $site->exists;
            $this->changeSiteType($site, $siteType, $externalUrl);
            if ($isNewSite){
                $this->publish($site);
            }
            return $site;
        });
    }

    // 公開
    public function publish(PortfolioSite $site): void
    {
        if (! $site->agreed_at) {
            throw new DomainException('利用規約に同意していないため公開できません。');
        }

        $site->is_public = true;
        $site->save();
    }

    // 設定画面で「公開を停止」を押す
    public function unpublish(PortfolioSite $site): void
    {
        $site->is_public = false;
        $site->save();
    }

    // 設定画面で方式を切り替える
    public function changeSiteType(PortfolioSite $site, SiteType $siteType, ?string $externalUrl = null): void
    {
        // 外部URL方式のときだけ、URLを検査する。代入より前に置くので、弾いたときは何も変わらない
        if ($siteType === SiteType::External) {
            $this->assertHttpsUrl($externalUrl);
        }

        $site->site_type    = $siteType;
        $site->external_url = $siteType === SiteType::External ? $externalUrl : null;

        $site->save();
    }

    private function assertHttpsUrl(?string $url, string $label = '外部URL'): void
    {
        $isValid = $url !== null
            && strlen($url) <= self::EXTERNAL_URL_MAX_LENGTH
            && filter_var($url, FILTER_VALIDATE_URL) !== false
            && strtolower((string) parse_url($url, PHP_URL_SCHEME)) === 'https';

        if(! $isValid) {
            throw new DomainException($label.'はhttps://で始まるURLのみ登録できます。');
        }
    }
    // 利用規約のバージョン管理
    public function needsReagreement(PortfolioSite $site): bool
    {
        return $site->terms_version !== config('terms.current_version');
    }

    //Token作成
    private function generateToken(): string
    {
        do {
            $token = Str::random(self::TOKEN_LENGTH);
        } while (PortfolioSite::where('token', $token)->exists());


        return $token;
    }

    // 
    public function updateDetails(PortfolioSite $site, array $details): void
    {
        // 1. 検査する。違反があればここで止まり、下の代入は1つも実行されない
        $validated = Validator::make($details, self::DETAIL_RULES,self::DETAIL_MESSAGES)->validate();

        // 2. 1つの値
        $site->school_name = $validated['school_name'] ?? null;
        $site->department  = $validated['department'] ?? null;
        $site->major       = $validated['major'] ?? null;
        $site->job_axis    = $validated['job_axis'] ?? null;

        // 3. リスト：決めたキーだけで作り直し、全部空の行を捨てる
        $site->hobbies        = array_values(array_filter($validated['hobbies'] ?? [], fn ($hobby) => ! blank($hobby)));
        $site->life_story     = $this->cleanRows($validated['life_story'] ?? [], ['period', 'detail']);
        $site->skills         = $this->cleanRows($validated['skills'] ?? [], ['name', 'detail']);
        $site->certifications = $this->cleanRows($validated['certifications'] ?? [], ['name', 'acquired']);
        $site->careers        = $this->cleanRows($validated['careers'] ?? [], ['period', 'content']);
        $site->awards         = $this->cleanRows($validated['awards'] ?? [], ['period', 'name', 'organizer', 'description']);
        $site->custom_sections = $this->cleanCustomSections($validated['custom_sections'] ?? []);

        // 4. 出す・出さない
        $site->visibility = $this->cleanVisibility($validated['visibility'] ?? []);

        // 5. 初めて保存した日時（2回目以降は最初の日時を残す）
        $site->details_saved_at ??= now();

        $site->save();
    }

    /**
     * テンプレートの保存。updateTemplate と updateDetails を1つのトランザクションで行う。
     * 片方が例外を投げたら、もう片方の保存も取り消される。
     */
    public function saveTemplate(PortfolioSite $site, ?string $displayName, ?string $bio, array $links, array $details): void
    {
        DB::transaction(function () use ($site, $displayName, $bio, $links, $details) {
            $this->updateTemplate($site, $displayName, $bio, $links);
            $this->updateDetails($site, $details);
        });
    }

    /**
     * 各行を $keys のキーだけの形に作り直し、全部空の行を捨てる。
     * $clean[] = で足すので、番号は 0 から詰め直される。
     */
    private function cleanRows(array $rows, array $keys): array
    {
        $clean = [];

        foreach ($rows as $row) {
            $picked = [];
            foreach ($keys as $key) {
                $picked[$key] = $row[$key] ?? null;
            }

            // 中身のあるキーが1つもなければ捨てる
            if (array_filter($picked, fn ($value) => ! blank($value)) === []) {
                continue;
            }

            $clean[] = $picked;
        }

        return $clean;
    }

    /**
     * 学生が足す項目。visible は true/false にそろえる。送られてこなければ「出す」。
     * 見出しも本文も空の行は、visible にかかわらず捨てる。
     */
    private function cleanCustomSections(array $rows): array
    {
        $clean = [];

        foreach ($rows as $row) {
            $title = $row['title'] ?? null;
            $body  = $row['body'] ?? null;

            if (blank($title) && blank($body)) {
                continue;
            }

            $clean[] = [
                'title'   => $title,
                'body'    => $body,
                'visible' => isset($row['visible']) ? filter_var($row['visible'], FILTER_VALIDATE_BOOLEAN) : true,
            ];
        }

        return $clean;
    }

    /**
     * 出す・出さないを true/false にそろえる。null のキーは入れない（キーがない＝出す）。
     */
    private function cleanVisibility(array $visibility): array
    {
        $clean = [];

        foreach ($visibility as $key => $value) {
            if ($value === null) {
                continue;
            }
            $clean[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return $clean;
    }

    private const DISPLAY_NAME_MAX_LENGTH = 50;
    private const BIO_MAX_LENGTH          = 1000;
    private const LINKS_MAX_COUNT         = 5;

    // 公開情報を変更
    public function updateTemplate(PortfolioSite $site, ?string $displayName, ?string $bio, array $links):void
    {
        if ($displayName !== null && mb_strlen($displayName) > self::DISPLAY_NAME_MAX_LENGTH){
            throw new DomainException('表示名は20文字以内で入力してください。');
        }

        if ($bio !== null && mb_strlen($bio) > self::BIO_MAX_LENGTH){
            throw new DomainException('自己紹介は500文字以内で入力してください。');
        }

        if (count($links) > self::LINKS_MAX_COUNT){
            throw new DomainException('リンクは5件まで登録できます。');
        }

        $cleanLinks = [];
        foreach ($links as $link) {
            $label = trim($link['label'] ?? '');
            $url   = trim($link['url'] ?? '');

            if ($label === '') {
                throw new DomainException('リンクの表示名を入力してください。');
            }

            if ($url === '') {
                throw new DomainException('リンクのURLを入力してください。');
            }

            $this->assertHttpsUrl($url, 'リンクのURL');

            if (mb_strlen($label) > self::LINK_LABEL_MAX_LENGTH) {
                throw new DomainException('リンクの表示名は30文字以内で入力してください。');
            }

            $cleanLinks[] = ['label' => $label, 'url' => $url];
        }

        $site->display_name = $displayName;
        $site->bio          = $bio;
        $site->links        = $cleanLinks;
        $site->save();
    }

    private const DETAIL_RULES = [
            // 1つの値
            'school_name' => ['nullable', 'string', 'max:30'],
            'department'  => ['nullable', 'string', 'max:20'],
            'major'       => ['nullable', 'string', 'max:20'],
            'job_axis'    => ['nullable', 'string', 'max:500'],
 
            // 趣味：単語のリスト
            'hobbies'   => ['nullable', 'array', 'max:10'],
            'hobbies.*' => ['nullable', 'string', 'max:20'],
 
            // 人生の道筋：年月と詳細は、どちらかがあれば両方必須
            'life_story'          => ['nullable', 'array', 'max:10'],
            'life_story.*'        => ['array'],
            'life_story.*.period'  => ['nullable', 'date_format:Y-m', 'required_with:life_story.*.detail'],
            'life_story.*.detail' => ['nullable', 'string', 'max:50', 'required_with:life_story.*.period'],
 
            // スキル：詳細があればスキル名が必須
            'skills'          => ['nullable', 'array', 'max:10'],
            'skills.*'        => ['array'],
            'skills.*.name'   => ['nullable', 'string', 'max:30', 'required_with:skills.*.detail'],
            'skills.*.detail' => ['nullable', 'string', 'max:30'],
 
            // 資格：取得時期があれば資格名が必須
            'certifications'            => ['nullable', 'array', 'max:10'],
            'certifications.*'          => ['array'],
            'certifications.*.name'     => ['nullable', 'string', 'max:30', 'required_with:certifications.*.acquired'],
            'certifications.*.acquired' => ['nullable', 'date_format:Y-m'],
 
            // 経歴と活動：時期があれば内容が必須
            'careers'           => ['nullable', 'array', 'max:20'],
            'careers.*'         => ['array'],
            'careers.*.period'   => ['nullable', 'date_format:Y-m'],
            'careers.*.content' => ['nullable', 'string', 'max:500', 'required_with:careers.*.period'],
 
            // 受賞歴：どれかがあれば受賞名が必須
            'awards'               => ['nullable', 'array', 'max:20'],
            'awards.*'             => ['array'],
            'awards.*.period'      => ['nullable', 'date_format:Y-m'],
            'awards.*.name'        => ['nullable', 'string', 'max:20', 'required_with:awards.*.period,awards.*.organizer,awards.*.description'],
            'awards.*.organizer'   => ['nullable', 'string', 'max:30'],
            'awards.*.description' => ['nullable', 'string', 'max:50'],
 
            // 学生が足す項目：見出しと本文は、どちらかがあれば両方必須
            'custom_sections'           => ['nullable', 'array', 'max:10'],
            'custom_sections.*'         => ['array'],
            'custom_sections.*.title'   => ['nullable', 'string', 'max:50', 'required_with:custom_sections.*.body'],
            'custom_sections.*.body'    => ['nullable', 'string', 'max:500', 'required_with:custom_sections.*.title'],
            'custom_sections.*.visible' => ['nullable', 'boolean'],
 
            // 出す・出さない：決めたキーだけ受け付ける（制作物の works は ③ で足す）
            'visibility'                => ['nullable', 'array'],
            'visibility.display_name'   => ['nullable', 'boolean'],
            'visibility.bio'            => ['nullable', 'boolean'],
            'visibility.links'          => ['nullable', 'boolean'],
            'visibility.school_name'    => ['nullable', 'boolean'],
            'visibility.department'     => ['nullable', 'boolean'],
            'visibility.major'          => ['nullable', 'boolean'],
            'visibility.job_axis'       => ['nullable', 'boolean'],
            'visibility.hobbies'        => ['nullable', 'boolean'],
            'visibility.life_story'     => ['nullable', 'boolean'],
            'visibility.skills'         => ['nullable', 'boolean'],
            'visibility.certifications' => ['nullable', 'boolean'],
            'visibility.careers'        => ['nullable', 'boolean'],
            'visibility.awards'         => ['nullable', 'boolean'],
        ];

        // updateDetails のエラー文。行のある項目は :position（1から数えた番号）で何行目かを出す。
        // ここにない組み合わせは lang/ja/validation.php の文の型と attributes の名前が使われる。
        private const DETAIL_MESSAGES = [
            // 年月の共通（下で項目ごとに書いたものが優先される）
            'date_format' => '年と月の両方を選んでください。',

            // 趣味
            'hobbies.*.max' => '趣味の:position個目：20文字以内で入力してください。',

            // 人生の道筋
            'life_story.*.period.required_with' => '人生の道筋の:position行目：詳細を書いた場合は、年月も選んでください。',
            'life_story.*.period.date_format'   => '人生の道筋の:position行目：年と月の両方を選んでください。',
            'life_story.*.detail.required_with' => '人生の道筋の:position行目：年月を選んだ場合は、詳細も入力してください。',
            'life_story.*.detail.max'           => '人生の道筋の:position行目：詳細は50文字以内で入力してください。',

            // スキル
            'skills.*.name.required_with' => 'スキルの:position行目：詳細を書いた場合は、スキル名も入力してください。',
            'skills.*.name.max'           => 'スキルの:position行目：スキル名は30文字以内で入力してください。',
            'skills.*.detail.max'         => 'スキルの:position行目：詳細は30文字以内で入力してください。',

            // 資格
            'certifications.*.name.required_with' => '資格の:position行目：取得時期を選んだ場合は、資格名も入力してください。',
            'certifications.*.name.max'           => '資格の:position行目：資格名は30文字以内で入力してください。',
            'certifications.*.acquired.date_format' => '資格の:position行目：取得時期は年と月の両方を選んでください。',

            // 経歴と活動
            'careers.*.period.date_format'   => '経歴の:position行目：時期は年と月の両方を選んでください。',
            'careers.*.content.required_with' => '経歴の:position行目：時期を選んだ場合は、内容も入力してください。',
            'careers.*.content.max'           => '経歴の:position行目：内容は500文字以内で入力してください。',

            // 受賞歴
            'awards.*.period.date_format'   => '受賞歴の:position行目：年と月の両方を選んでください。',
            'awards.*.name.required_with'   => '受賞歴の:position行目：年月・主催・説明のどれかを書いた場合は、受賞名も入力してください。',
            'awards.*.name.max'             => '受賞歴の:position行目：受賞名は20文字以内で入力してください。',
            'awards.*.organizer.max'        => '受賞歴の:position行目：主催は30文字以内で入力してください。',
            'awards.*.description.max'      => '受賞歴の:position行目：説明は50文字以内で入力してください。',

            // 学生が足す項目
            'custom_sections.*.title.required_with' => '追加した項目の:position行目：本文を書いた場合は、見出しも入力してください。',
            'custom_sections.*.title.max'           => '追加した項目の:position行目：見出しは50文字以内で入力してください。',
            'custom_sections.*.body.required_with'  => '追加した項目の:position行目：見出しを書いた場合は、本文も入力してください。',
            'custom_sections.*.body.max'            => '追加した項目の:position行目：本文は500文字以内で入力してください。',
        ];
}