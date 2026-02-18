<?php
namespace App\Providers;

use Illuminate\Auth\Events\Login;
use app\Listeners\LogSuccessfulLogin;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen =[
    Login::class => [
        LogSuccessfulLogin::class,
    ],
];

}
?>