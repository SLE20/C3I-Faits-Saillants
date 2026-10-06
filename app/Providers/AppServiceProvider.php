<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
{
    $this->app->bind(
        \Backpack\CRUD\app\Http\Middleware\AuthenticateSession::class,
        \App\Http\Middleware\AuthenticateBackpackSession::class
    );
}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
