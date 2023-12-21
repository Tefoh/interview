<?php

namespace App\Providers;

use App\Services\Article\ArticleService;
use App\Services\Article\ArticleServiceInterface;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            ArticleServiceInterface::class,
            ArticleService::class,
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
