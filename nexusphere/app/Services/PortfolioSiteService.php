<?php

namespace App\Services;

use App\Enums\SiteType;
use App\Models\PortfolioSite;
use App\Models\User;
use DomainException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

// ポートフォリオの作成に必要なルールを守るための処理
class PortfolioSiteService
{
    private const TOKEN_LENGTH = 16;

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

            $site->site_type     = $siteType; 
            $site->agreed_at     = now();
            $site->terms_version = config('terms.current_version');
            $site->save();

            $this->changeSiteType($site, $siteType, $externalUrl);
            $this->publish($site);
            
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
        $site->site_type    = $siteType;
        $site->external_url = $siteType === SiteType::External ? $externalUrl : null;
        $site->save();
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
}