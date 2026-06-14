<?php declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\LoginHistory;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        LoginHistory::create([
            'user_id' => $event->user->user_id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'login_at' => now(),
        ]);
    }
}
