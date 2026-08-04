<?php

use App\Http\Controllers\Admin\AdminLocaleController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CkeditorController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LanguagesController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TranslationController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware(['auth', 'admin.access', 'admin.locale'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::post('/locale', [AdminLocaleController::class, 'set'])->name('locale.set');

    Route::patch('languages/{language}/toggle-status', [LanguagesController::class, 'toggleStatus'])
        ->name('languages.toggle-status');
    Route::patch('languages/{language}/set-default-admin', [LanguagesController::class, 'setDefaultAdmin'])
        ->name('languages.set-default-admin');
    Route::patch('languages/{language}/set-default-site', [LanguagesController::class, 'setDefaultSite'])
        ->name('languages.set-default-site');
    Route::patch('languages/{language}/toggle-required', [LanguagesController::class, 'toggleRequired'])
        ->name('languages.toggle-required');

    Route::resource('languages', LanguagesController::class)
        ->parameters(['languages' => 'language'])
        ->except(['show']);

    Route::patch('translations/{translation}/value', [TranslationController::class, 'updateValue'])
        ->name('translations.update-value');

    Route::post('translations/sync/start', [TranslationController::class, 'startSync'])
        ->name('translations.sync.start');

    Route::post('translations/auto-translate/start', [TranslationController::class, 'startAutoTranslateMissing'])
        ->name('translations.auto-translate.start');

    Route::post('translations/auto-translate-google/start', [TranslationController::class, 'startAutoTranslateGoogle'])
        ->name('translations.auto-translate-google.start');

    Route::get('translations/progress/{token}', [TranslationController::class, 'progress'])
        ->name('translations.progress');

    Route::get('translations/export', [TranslationController::class, 'export'])
        ->name('translations.export');

    Route::post('translations/import/start', [TranslationController::class, 'startImport'])
        ->name('translations.import.start');

    Route::resource('translations', TranslationController::class)->except(['show']);

    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

    Route::resource('countries', CountryController::class)->except(['show']);
    Route::patch('countries/{country}/toggle-status', [CountryController::class, 'toggleStatus'])
        ->name('countries.toggle-status');

    Route::post('ckeditor/upload', [CkeditorController::class, 'upload'])
        ->name('ckeditor.upload');
});
