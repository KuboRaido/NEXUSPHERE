<?php declare(strict_types=1);

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use App\Listeners\LogSuccessfulLogin;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            LogSuccessfulLogin::class,
        ],
    ];
}