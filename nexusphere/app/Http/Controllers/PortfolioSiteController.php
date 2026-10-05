<?php

namespace App\Http\Controllers;

use app\Models\PortfolioSite;
use App\Enums\SiteType;
use App\Services\PortfolioSiteService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PortfolioSiteController extends Controller
{
    public function show(String $token){
        // 公開中のトークンだけを探す。見つからなければ自動で404
        $site = PortfolioSite::where('token', $token)
            ->where('is_public', true)
            ->firstOrFail();

        // 画面に返しつつ、検索エンジンに載せないヘッダを付ける
        return response()
            ->view('portfolio.show', ['site' => $site])
            ->header('X-Robots-Tag', 'noindex');
    }

    // サイト作成
    public function agree(Request $request, PortfolioSiteService $service)
    {
        $validated = $request->validate([
            'agreed'       => ['accepted', 'boolean'],
            'site_type'    => ['required', Rule::enum(SiteType::class)],
            'external_url' => ['nullable', 'string'],
        ]);

        try {
            $service->agreeAndCreate(
                $request->user(),                          // 自分。URLのIDは使わない
                SiteType::from($validated['site_type']),   // 文字列 'external' → Enum に変換
                $validated['external_url'] ?? null
            );
        } catch (DomainException $e) {
            return back()->withErrors(['external_url' => $e->getMessage()]);
        }

        return back()->with('status', '公開サイトを作成しました。');
    }

    // サイトタイプを変更
    public function changeType(Request $request, PortfolioSiteService $service)
    {
        $validated = $request->validate([
            'site_type'    => ['required', Rule::enum(SiteType::class)],
            'external_url' => ['nullable', 'string'],
        ]);

        $site = $this->ownSite($request);

        try {
            $service->changeSiteType(
                $site,
                SiteType::from($validated['site_type']),
                $validated['external_url'] ?? null);
        } catch (DomainException $e) {
            return back()->withErrors(['external_url' => $e->getMessage()])->withInput();
        }

        return back()->with('status', '公開サイトの方式を変更しました。');
    }

    public function publish(Request $request, PortfolioSiteService $service)
    {
        $site = $this->ownSite($request);

        try {
            $service->publish($site);
        } catch (DomainException $e) {
            return back()->withErrors(['portfolio_site' => $e->getMessage()])->withInput();
        }

        return back()->with('status', '公開サイトを公開しました。');
    }

    //公開サイトを非公開にする
    public function unpublish(Request $request, PortfolioSiteService $service)
    {
        $site = $this->ownSite($request);

        $service->unpublish($site);

        return back()->with('status', '公開サイトを非公開にしました。');
    }

    // 設定画面を表示・規約に同意していない場合は規約の同意foamを表示
    public function edit () {

    }

    // 公開サイトの情報を修正したものをアップデート
    public function updateTemplate (Request $request, PortfolioSiteService $service) {
        $validated = $request->validate([
            'display_name'  => ['nullable', 'string'],
            'bio'           => ['nullable', 'string'],
            'links'         => ['nullable', 'array'],
            'links.*.label' => ['nullable', 'string'],
            'links.*.url'   => ['nullable', 'string'],
        ]);

        $site = $this->ownSite($request);

         // URLが空の行は捨てて、番号を0から振り直す
        $links = array_values(array_filter(
            $validated['links'] ?? [],
            fn ($link) => ($link['url'] ?? '') !== ''
        ));

        try {
            $service->updateTemplate($site, $validated['display_name'] ?? null, $validated['bio'] ?? null, $links);
        } catch (DomainException $e) {
            return back()->withErrors(['portfolio_site' => $e->getMessage()])->withInput();
        }

        return back()->with('status', '公開サイトの内容を保存しました。');
    }

    // ログイン中のUserの公開siteを取って、なければ404を返す
    private function ownSite(Request $request): PortfolioSite
    {
        return $request->user()->portfolioSite ?? abort(404);
    }
}
