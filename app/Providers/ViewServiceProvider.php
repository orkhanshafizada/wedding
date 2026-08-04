<?php
namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Front layout + header + footer üçün ümumi settings-lər
//        View::composer([
//            'web.layouts.app',
//            'web.partials.header',
//            'web.partials.footer',
//            'web.account.dashboard',
//        ], SiteLayoutComposer::class);
    }
}
