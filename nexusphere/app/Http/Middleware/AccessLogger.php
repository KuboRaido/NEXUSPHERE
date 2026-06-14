<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use App\Models\AccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AccessLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            AccessLog::create([
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'path' => $request->path(),
                'access_at' => now(),
            ]);
        }

        return $next($request);
    }
}
