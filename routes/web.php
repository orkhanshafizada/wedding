<?php

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Web\SearchController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\MenuPageController;
use App\Http\Controllers\DocController;


use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

//
// PUBLIC ROUTES (guest)
//
Route::middleware('guest:customer')->group(function () {
    // Guest web routes may be added here in the future
});

//
// AUTHENTICATED CUSTOMER ROUTES
//
Route::middleware(['auth:customer'])->group(function () {
});

//
// PUBLIC GENERAL ROUTES
//
Route::get('/', [HomeController::class, 'index'])->name('web.home');


Route::get('api/documentation', [DocController::class, 'index']);
Route::get('/documentation/endpoints', [DocController::class, 'getApiDocs']);

Route::get('/search', SearchController::class)->name('web.search');


/**
 *  DYNAMIC MENU ROUTE (CATCH-ALL)
 *
 *  Only URLs matching the following conditions will fall here:
 *   - Does not start with 'account', 'ayti', 'api', 'documentation', 'ajax'
 *   - Has not already been defined as a separate route above
 *
 *  Examples:
 *   - /about-us          -> MenuPageController
 *   - /artists           -> MenuPageController
 *   - /contact           -> MenuPageController
 *
 *  Note:
 *   - Root "/" is already handled by web.home, so {any} will not be an empty string.
 */
Route::get('{any}', MenuPageController::class)
    ->where('any', '^(?!account)(?!ayti)(?!api)(?!documentation)(?!ajax).*')
    ->name('web.menu.page');
