<?php
namespace App\Providers;

use App\Translation\DatabaseLoader;
use Illuminate\Support\ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('translation.loader', function ($app) {
            return new DatabaseLoader(
                $app->make('db')->connection(),
                'translations'
            );
        });
    }

    public function boot(): void
    {
        // Nothing else needed; Translator will use our loader via the container.
    }
}
