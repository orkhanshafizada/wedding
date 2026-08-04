<?php

use Illuminate\Support\Facades\Route;
use Modules\Grids\Http\Controllers\Admin\GridAjaxController;
use Modules\Grids\Http\Controllers\Admin\GridController;

Route::prefix('grids')
    ->as('grids.')
    ->group(function () {
        Route::get('/ajax/related-products', [GridAjaxController::class, 'relatedProducts'])
            ->name('ajax.related-products')
            ->middleware('permission:grids.view');
    });

Route::prefix('menus/{menu}/grids')
    ->as('grids.')
    ->group(function () {

        Route::get('/', [GridController::class, 'index'])
            ->name('index')
            ->middleware('permission:grids.view');

        Route::get('/create', [GridController::class, 'create'])
            ->name('create')
            ->middleware('permission:grids.create');

        Route::post('/', [GridController::class, 'store'])
            ->name('store')
            ->middleware('permission:grids.create');

        Route::get('/{grid}/edit', [GridController::class, 'edit'])
            ->name('edit')
            ->middleware('permission:grids.edit');

        Route::put('/{grid}', [GridController::class, 'update'])
            ->name('update')
            ->middleware('permission:grids.edit');

        Route::delete('/{grid}', [GridController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:grids.delete');

        Route::post('/bulk-delete', [GridController::class, 'bulkDelete'])
            ->name('bulk-delete')
            ->middleware('permission:grids.delete');

        Route::post('/update-order', [GridController::class, 'updateOrder'])
            ->name('update-order')
            ->middleware('permission:grids.edit');
    });
