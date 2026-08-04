<?php

use App\Http\Middleware\AdminAccess;
use App\Http\Middleware\AdminSetLocale;
use App\Http\Middleware\ApiLoggingMiddleware;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SetLocaleFromHeader;
use App\Http\Middleware\VerifyCsrfToken;
use App\Support\RouteRegistrar;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Modules\AdminPermission\Http\Middleware\AdminPermissionMiddleware;
use Modules\AdminPermission\Providers\AdminPermissionServiceProvider;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(function (): void {
        RouteRegistrar::register();
    }, commands: base_path('routes/console.php'), health: '/up')
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => Authenticate::class,
            'guest' => RedirectIfAuthenticated::class,
            'admin.locale' => AdminSetLocale::class,
            'admin.access' => AdminAccess::class,
            'verify.csrf' => VerifyCsrfToken::class,
            'set.locale.header' => SetLocaleFromHeader::class,
            'customer.api.log' => ApiLoggingMiddleware::class,
            'permission' => AdminPermissionMiddleware::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('ayti') || $request->is('ayti/*')) {
                return route('admin.login');
            }

            if ($request->is('account') || $request->is('account/*')) {
                return route('account.login');
            }

            return url('/');
        });
    })
    ->withProviders([
        App\Providers\AppServiceProvider::class,
        App\Providers\TranslationServiceProvider::class,
        App\Providers\ViewServiceProvider::class,
        App\Providers\AdminViewServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
    })
    ->create();
