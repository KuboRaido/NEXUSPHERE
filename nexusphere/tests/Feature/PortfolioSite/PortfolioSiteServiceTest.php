<?php

use App\Enums\SiteType;
use App\Models\PortfolioSite;
use App\Models\User;
use App\Services\PortfolioSiteService;

beforeEach(function () {
    $this->service = app(PortfolioSiteService::class);
});

it('同意すると公開サイトが作られ、同意日時と規約の版が記録される', function () {
    $user = User::factory()->create();

    $site = $this->service->agreeAndCreate($user, SiteType::Template);

    expect($site->agreed_at)->not->toBeNull();
    expect($site->terms_version)->toBe(config('terms.current_version'));
    expect($site->token)->toHaveLength(16);
    expect($site->is_public)->toBeTrue();
});

it('トークンは利用者ごとに異なる', function () {
    $a = $this->service->agreeAndCreate(User::factory()->create(), SiteType::Template);
    $b = $this->service->agreeAndCreate(User::factory()->create(), SiteType::Template);

    expect($a->token)->not->toBe($b->token);
});

it('同意していない公開サイトは公開できない', function () {
    $user = User::factory()->create();
    $site = PortfolioSite::create([
        'user_id'   => $user->user_id,
        'token'     => 'aaaaaaaaaaaaaaaa',
        'site_type' => SiteType::Template,
    ]);

    $this->service->publish($site);
})->throws(DomainException::class);

it('公開を停止できる', function () {
    $site = $this->service->agreeAndCreate(User::factory()->create(), SiteType::Template);

    $this->service->unpublish($site);

    expect($site->fresh()->is_public)->toBeFalse();
});

it('外部URL方式に切り替えられる', function () {
    $site = $this->service->agreeAndCreate(User::factory()->create(), SiteType::Template);

    $this->service->changeSiteType($site, SiteType::External, 'https://example.com/portfolio');

    $site->refresh();
    expect($site->site_type)->toBe(SiteType::External);
    expect($site->external_url)->toBe('https://example.com/portfolio');
});

it('規約が改定されたら再同意が必要と判定される', function () {
    $site = $this->service->agreeAndCreate(User::factory()->create(), SiteType::Template);

    expect($this->service->needsReagreement($site))->toBeFalse();

    config(['terms.current_version' => '2027-01']);

    expect($this->service->needsReagreement($site))->toBeTrue();
});

it('同意し直してもトークンは変わらない', function () {
    $user  = User::factory()->create();
    $first = $this->service->agreeAndCreate($user, SiteType::Template);
    $tokenBefore = $first->token;

    $second = $this->service->agreeAndCreate($user, SiteType::Template);

    expect($second->token)->toBe($tokenBefore);
});

it('テンプレート方式に戻すと外部URLは消える', function () {
    $site = $this->service->agreeAndCreate(
        User::factory()->create(),
        SiteType::External,
        'https://example.com'
    );

    $this->service->changeSiteType($site,SiteType::Template);

    $site->refresh();
    expect($site->site_type)->toBe(SiteType::Template);
    expect($site->external_url)->toBeNull();
});

it('https以外の外部URLは登録できない', function(string $url) {
    // テンプレート方式のサイトを作る
    $site = $this->service->agreeAndCreate(User::factory()->create(), SiteType::Template);

    $this->service->changeSiteType($site,SiteType::External, $url);
})->with([
    'javascript'     => 'javascript:alert(1)',
    'javascript偽装' => 'javascript://comment%0Aalert(1)',
    'data'           => 'data:text/html,<script>alert(1)</script>',
    'http'           => 'http://example.com',
    'スキームのみ'    => 'https://',
    '先頭に空白'      => ' https://example.com',
    '途中に空白' => 'https://exa mple.com',
])->throws(DomainException::class);

it('外部URL方式でURLが空なら登録できない', function() {
    $site = $this->service->agreeAndCreate(User::factory()->create(), SiteType::Template);

    $this->service->changeSiteType($site,SiteType::External, null);
})->throws(DomainException::class);

it('不正なURLで同意したときは公開サイトが作られない', function () {
    $user = User::factory()->create();

    // 例外が出ることを確かめる（テストはここで止まらない）
    expect(fn () => $this->service->agreeAndCreate($user, SiteType::External, 'javascript:alert(1)'))
        ->toThrow(DomainException::class);

    // そのあと、DBに行が残っていないことを確かめる
    expect(PortfolioSite::where('user_id', $user->user_id)->exists())->toBeFalse();
});

it('外部URL方式で保存できる',function() {
    $site = $this->service->agreeAndCreate(User::factory()->create(),SiteType::External,'https://example.com');

    $site->refresh();
    expect($site->site_type)->toBe(SiteType::External);
    expect($site->external_url)->toBe('https://example.com');
});

it('公開を停止していた人は、再同意しても非公開のまま',function() {
    $user = User::factory()->create();
    $site = $this->service->agreeAndCreate($user,SiteType::Template);

    $this->service->unpublish($site);

    $site = $this->service->agreeAndCreate($user,SiteType::Template);
    $site->refresh();
    expect($site->is_public)->toBeFalse();
});

it('テンプレートの表示名・自己紹介・リンクを保存できる', function () {
    $site = $this->service->agreeAndCreate(User::factory()->create(), SiteType::Template);

    $this->service->updateTemplate($site, 'らいど', 'バックエンドを中心に学んでいます。', [
        ['label' => 'GitHub', 'url'=> 'https://github.com/example'],
    ]);

    $site->refresh();
    expect($site->display_name)->toBe('らいど');
    expect($site->bio)->toBe('バックエンドを中心に学んでいます。');
    expect($site->links)->toEqual([
        ['label' => 'GitHub', 'url' => 'https://github.com/example'],
    ]);
});

it('リンクのURLがhttps以外なら弾かれる', function (string $url) {
    $site = $this->service->agreeAndCreate(User::factory()->create(), SiteType::Template);

    $this->service->updateTemplate($site, 'らいど', 'バックエンドを中心に学んでいます。', [['label' => 'x', 'url' => $url]]);
})->with([
    'javascript'     => 'javascript:alert(1)',
    'http'           => 'http://example.com',
])->throws(DomainException::class);

it('リンクが6件以上なら弾かれる', function () {
    $site = $this->service->agreeAndCreate(User::factory()->create(), SiteType::Template);

    $this->service->updateTemplate($site, 'らいど', 'バックエンドを中心に学んでいます。', array_fill(0, 6, ['label' => 'x', 'url' => 'https://example.com']));
})->throws(DomainException::class);

it('弾かれた時、元の中身は変わらない', function () {
    $site = $this->service->agreeAndCreate(User::factory()->create(), SiteType::Template);

    $this->service->updateTemplate($site, 'らいど', 'バックエンドを中心に学んでいます。', [
        ['label' => 'GitHub', 'url'=> 'https://github.com/example'],
    ]);

    expect(fn () => $this->service->updateTemplate($site, '書き替え', '書き替え',
        [['label' => 'x', 'url' => 'http://example.com']]
        ))->toThrow(DomainException::class);

    $site->refresh();
    expect($site->display_name)->toBe('らいど');
});