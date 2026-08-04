<?php

use Illuminate\Support\Facades\Route;
use Modules\MainPage\Http\Controllers\Api\MainPageApiController;

Route::prefix('main-page')
    ->as('main-page.')
    ->group(function (): void {
        Route::get('/', [MainPageApiController::class, 'index'])->name('index');
    });
