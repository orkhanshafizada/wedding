<?php

namespace App\Providers;

use App\Services\AutoTranslator\DriverInterface;
use App\Services\AutoTranslator\GoogleAutoTranslator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            DriverInterface::class,
            GoogleAutoTranslator::class
        );
    }

    public function boot(): void
    {
        URL::forceScheme('https');

        Paginator::useBootstrapFive();
    }
}
