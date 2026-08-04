<?php

use Illuminate\Support\Facades\Route;
use Modules\LogosPartners\Http\Controllers\Admin\LogosPartnerController;

Route::prefix('menus/{menu}/logospartners')
    ->as('logospartners.')
    ->group(function () {
        Route::get('/', [LogosPartnerController::class, 'index'])->name('index')->middleware('permission:logospartners.view');
        Route::get('/create', [LogosPartnerController::class, 'create'])->name('create')->middleware('permission:logospartners.create');
        Route::post('/', [LogosPartnerController::class, 'store'])->name('store')->middleware('permission:logospartners.create');
        Route::get('/{logosPartner}/edit', [LogosPartnerController::class, 'edit'])->name('edit')->middleware('permission:logospartners.edit');
        Route::put('/{logosPartner}', [LogosPartnerController::class, 'update'])->name('update')->middleware('permission:logospartners.edit');
        Route::delete('/{logosPartner}', [LogosPartnerController::class, 'destroy'])->name('destroy')->middleware('permission:logospartners.delete');
        Route::post('/update-order', [LogosPartnerController::class, 'updateOrder'])->name('update-order')->middleware('permission:logospartners.edit');
    });
