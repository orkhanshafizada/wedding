<?php
namespace App\Support;

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminSetLocale;

class RouteRegistrar
{
    public static function register(): void
    {
        Route::middleware('web')->group(base_path('routes/web.php'));

        Route::prefix('api/v1')
            ->middleware('api')
            ->as('api.v1.')
            ->group(base_path('routes/api.php'));

        Route::prefix('ayti')->as('admin.')
            ->middleware(['web', 'admin.locale'])
            ->group(base_path('routes/admin.php'));
    }
}
