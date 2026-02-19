<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\AccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AccessLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //Auth::Check（）ユーザーがログインしているかチェック
        if(Auth::check()){
            AccessLog::create([
                'user_id' => Auth::id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'path'      => request()->path(),
                'access_at' => now(),
            ]);
        }
        return $next($request);
    }
}
