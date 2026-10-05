<?php

use App\Enums\SiteType;
use App\Models\User;
use App\Services\PortfolioSiteService;
use App\Models\PortfolioSite;

beforeEach(function () {
    $this->service = app(PortfolioSiteService::class);
});

it('公開中のページは誰でも見られて、noindexが付いている', function () {
    //同意して公開中のサイトを作る
    $site = app(PortfolioSiteService::class)
        ->agreeAndCreate(User::factory()->create(), SiteType::Template);

    //ログインせずにアクセス
    $response = $this->get('/p/' . $site->token);

    //確認
    $response->assertOk();
    $response->assertHeader('X-Robots-Tag', 'noindex');
});

it('非公開のページは404になる', function() {
    $site = app(PortfolioSiteService::class)
        ->agreeAndCreate(User::factory()->create(), SiteType::Template);

    $this->service->unpublish($site);

    $response = $this->get('/p/' . $site->token);

    $response->assertNotFound();
});

it('存在しないトークンは404になる', function() {
    $response = $this->get('/p/abcd1234efgh5678');
    
    $response->assertNotFound();
});

it('未ログインでは設定系のルートを使えない', function() {
    $response = $this->post('/portfolio-site/agree');

    $response->assertRedirect(route('login'));
});

it('ログインしていてもGETでは方式を変更できない', function () {
    $user = User::factory()->create();
    $site = $this->service->agreeAndCreate($user, SiteType::Template);

    // 攻撃者が踏ませるURLと同じものに、GETでアクセスする
    $response = $this->actingAs($user)
        ->get('/portfolio-site/type?site_type=external&external_url=https://evil.example');

    $response->assertMethodNotAllowed();

    $site->refresh();
    expect($site->site_type)->toBe(SiteType::Template);
});

it('規約の同意にチェックが入っているか', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/portfolio-site');

    $response->assertOk();
});

it('同意にチェックがなければ公開サイトは作られない', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/portfolio-site/agree', [
        'site_type' => 'template',
    ]);

    $response->assertSessionHasErrors('agreed');
    expect(PortfolioSite::where('user_id', $user->user_id)->exists())->toBeFalse();
});

it('リンクの空の行は捨てて保存される', function () {
    $user = User::factory()->create();
    $site = $this->service->agreeAndCreate($user, SiteType::Template);

    $response = $this->actingAs($user)->put('/portfolio-site/template', ['display_name' => 'らいど', 'bio' => 'バックエンドを中心に学んでいます。', 'links' => [['label' => 'GitHub', 'url' => "https://github.com/example"],['label' => '', 'url' => ""]]]);
    $site->refresh();

    $response->assertRedirect(); 
    expect($site->links)->toEqual([
        ['label' => 'GitHub', 'url' => 'https://github.com/example'],
    ]);
});
