<?php

use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\GuestTokenController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TranslationApiController;
use App\Http\Controllers\DocController;
use Illuminate\Support\Facades\Route;

Route::get('/documentation', [DocController::class, 'index']);
Route::get('/documentation/endpoints', [DocController::class, 'getApiDocs']);

Route::get('/settings', [SettingController::class, 'index'])
    ->middleware(['set.locale.header', 'customer.api.log'])
    ->name('api.settings.index');

Route::post('/guest-token', [GuestTokenController::class, 'create'])
    ->middleware(['set.locale.header', 'customer.api.log'])
    ->name('api.settings.guest-token.create');

Route::get('/countries', [CountryController::class, 'index'])
    ->middleware(['set.locale.header', 'customer.api.log'])
    ->name('api.settings.countries.index');

Route::get('/languages', [LanguageController::class, 'index'])
    ->middleware(['set.locale.header', 'customer.api.log'])
    ->name('api.settings.languages.index');

Route::get('/translations', [TranslationApiController::class, 'index'])
    ->middleware(['set.locale.header', 'customer.api.log'])
    ->name('api.settings.translations.index');
