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