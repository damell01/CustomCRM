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
        /*
         * Hostinger flat-structure fix.
         *
         * When APP_FLAT_PUBLIC=true the entire project lives inside public_html/
         * and there is no separate /public subfolder — its contents were merged
         * into the root. We rebind path.public so Laravel resolves asset paths,
         * storage:link, and Vite manifest lookups against the root instead of
         * the non-existent {base}/public directory.
         */
        if (config('app.flat_public', env('APP_FLAT_PUBLIC', false))) {
            $this->app->bind('path.public', fn () => base_path());
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
