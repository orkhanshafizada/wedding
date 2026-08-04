<?php

use Illuminate\Support\Facades\Route;
use Modules\Faq\Http\Controllers\Admin\FAQController;

Route::prefix('menus/{menu}/faq')
    ->as('faq.')
    ->group(function () {

                Route::get('/', [FAQController::class, 'index'])
                    ->name('index');

                Route::post('/update-order', [FAQController::class, 'updateOrder'])
                    ->name('update-order')
                    ->middleware('permission:faq.edit');

                Route::get('/create', [FAQController::class, 'create'])
                    ->name('create')
                    ->middleware('permission:faq.create');

                Route::post('/', [FAQController::class, 'store'])
                    ->name('store')
                    ->middleware('permission:faq.create');

                Route::get('/{faq}/edit', [FAQController::class, 'edit'])
                    ->name('edit')
                    ->middleware('permission:faq.edit');

                Route::put('/{faq}', [FAQController::class, 'update'])
                    ->name('update')
                    ->middleware('permission:faq.edit');

                Route::delete('/{faq}', [FAQController::class, 'destroy'])
                    ->name('destroy')
                    ->middleware('permission:faq.delete');
    });
