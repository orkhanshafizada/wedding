<?php

namespace App\Providers;

use App\Support\AdminLanguages;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AdminViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AdminLanguages::class, fn () => new AdminLanguages());
    }

    public function boot(): void
    {
        View::composer(['admin.*', '*::admin.*'], function ($view) {
            $adminLanguages = app(AdminLanguages::class);

            $view->with([
                'languages' => $adminLanguages->languages(),
                'requiredLanguages' => $adminLanguages->requiredLanguages(),
                'requiredLanguageIds' => $adminLanguages->requiredLanguageIds(),
                'activeLanguageIds' => $adminLanguages->activeLanguageIds(),
                'requiredLanguageCodes' => $adminLanguages->requiredLanguageCodes(),
                'activeLanguageCodes' => $adminLanguages->activeLanguageCodes(),
                'adminDefaultLanguage' => $adminLanguages->adminDefaultLanguage(),
                'adminDefaultLanguageId' => $adminLanguages->adminDefaultLanguageId(),
                'adminDefaultLanguageCode' => $adminLanguages->adminDefaultLanguageCode(),
            ]);
        });
    }
}
