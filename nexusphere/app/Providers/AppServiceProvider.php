<?php declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        if (str_contains(request()->getHost(), 'ngrok-free.dev') || $this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
