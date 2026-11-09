<?php

namespace App\Providers;

use App\Services\InAppNotificationService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('partials.notifications-menu', function ($view): void {
            $view->with(
                'notificationSummary',
                app(InAppNotificationService::class)->summary(),
            );
        });
    }
}
