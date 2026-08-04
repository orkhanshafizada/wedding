<?php

use Illuminate\Support\Facades\Route;
use Modules\Menu\Http\Controllers\Api\MenuController;
use Modules\Menu\Http\Controllers\Api\MenuDetailController;

Route::as('menus.')
    ->prefix('menus')
    ->group(function (): void {
        Route::get('/', [MenuController::class, 'index'])->name('index');
        Route::get('/detail', [MenuDetailController::class, 'show'])->name('detail');
    });
