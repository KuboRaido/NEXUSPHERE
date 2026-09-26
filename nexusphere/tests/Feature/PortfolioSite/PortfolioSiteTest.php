<?php

use App\Enums\SiteType;
use App\Models\PortfolioSite;
use App\Models\User;
use Illuminate\Database\QueryException;

it('公開サイトを作成できる', function () {
    $user = User::factory()->create();

    $site = PortfolioSite::create([
        'user_id'   => $user->user_id,
        'token'     => 'abcdefghij123456',
        'site_type' => SiteType::Template,
        'links'     => ['github' => 'https://github.com/KuboRaido'],
    ]);

    $site->refresh();

    expect($site->is_public)->toBeFalse();
    expect($site->links['github'])->toBe('https://github.com/KuboRaido');
    expect($site->site_type)->toBe(SiteType::Template);
});

it('同じユーザーが2つ持てない', function () {
    $user = User::factory()->create();

    PortfolioSite::create([
        'user_id' => $user->user_id, 'token' => 'aaaaaaaaaaaaaaaa', 'site_type' => SiteType::Template,
    ]);

    PortfolioSite::create([
        'user_id' => $user->user_id, 'token' => 'bbbbbbbbbbbbbbbb', 'site_type' => SiteType::Template,
    ]);
})->throws(QueryException::class);

it('同じトークンは登録できない', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();

    PortfolioSite::create([
        'user_id' => $a->user_id, 'token' => 'cccccccccccccccc', 'site_type' => SiteType::Template,
    ]);

    PortfolioSite::create([
        'user_id' => $b->user_id, 'token' => 'cccccccccccccccc', 'site_type' => SiteType::Template,
    ]);
})->throws(QueryException::class);