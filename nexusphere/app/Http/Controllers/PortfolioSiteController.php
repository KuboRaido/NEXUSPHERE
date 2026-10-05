<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use app\Models\PortfolioSite;

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
}
