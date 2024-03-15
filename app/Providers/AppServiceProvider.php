<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MailerService;
use App\Services\QueueService;

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

        $this->app->singleton(QueueService::class, function ($app) {
            return new QueueService(app(MailerService::class));
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
