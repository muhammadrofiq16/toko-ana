<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer('*', function ($view) {
            static $appSettings = null;

            if ($appSettings === null) {
                try {
                    $appSettings = Schema::hasTable('settings')
                        ? Setting::pluck('value', 'key')->toArray()
                        : [];
                } catch (\Throwable $e) {
                    $appSettings = [];
                }
            }

            $view->with('appSettings', $appSettings);
        });
    }
}
