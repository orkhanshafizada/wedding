<?php

use Illuminate\Support\Facades\Route;
use Modules\MainPage\Http\Controllers\Admin\MainPageSectionController;

Route::prefix('main-page')
    ->as('main_page.')
    ->group(function () {
        Route::get('/sections', [MainPageSectionController::class, 'index'])
            ->middleware('permission:main_page.view')
            ->name('sections.index');

        Route::get('/sections/create', [MainPageSectionController::class, 'create'])
            ->middleware('permission:main_page.create')
            ->name('sections.create');

        Route::post('/sections', [MainPageSectionController::class, 'store'])
            ->middleware('permission:main_page.create')
            ->name('sections.store');

        Route::get('/sections/{section}/edit', [MainPageSectionController::class, 'edit'])
            ->middleware('permission:main_page.edit')
            ->name('sections.edit');

        Route::put('/sections/{section}', [MainPageSectionController::class, 'update'])
            ->middleware('permission:main_page.edit')
            ->name('sections.update');

        Route::delete('/sections/{section}', [MainPageSectionController::class, 'destroy'])
            ->middleware('permission:main_page.delete')
            ->name('sections.destroy');

        Route::post('/sections/update-order', [MainPageSectionController::class, 'updateOrder'])
            ->middleware('permission:main_page.edit')
            ->name('sections.update-order');

        Route::get('/ajax/source-references/{sourceType}', [MainPageSectionController::class, 'sourceReferences'])
            ->middleware('permission:main_page.view')
            ->name('ajax.source-references');
    });
