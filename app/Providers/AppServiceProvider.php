<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use \App\Services\MailerService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MailerService::class, function ($app) {
            return new MailerService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
