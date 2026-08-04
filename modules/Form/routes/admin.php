<?php

use Illuminate\Support\Facades\Route;
use Modules\Form\Http\Controllers\Admin\FormController;
use Modules\Form\Http\Controllers\Admin\FormLabel\FormLabelController;
use Modules\Form\Http\Controllers\Admin\FormText\FormTextController;

Route::prefix('menus/{menu}')
    ->group(function (): void {
        Route::prefix('form')
            ->as('form.')
            ->group(function (): void {
                Route::get('/', [FormController::class, 'index'])
                    ->name('index')
                    ->middleware('permission:form.view');

                Route::get('/view-data', [FormController::class, 'viewData'])
                    ->name('view-data')
                    ->middleware('permission:form.view');

                Route::get('/export-data', [FormController::class, 'exportData'])
                    ->name('export-data')
                    ->middleware('permission:form.view');

                Route::patch('/responses/{response}/status', [FormController::class, 'updateResponseStatus'])
                    ->name('response.status')
                    ->middleware('permission:form.edit');

                Route::delete('/responses/{response}', [FormController::class, 'destroy'])
                    ->name('response.destroy')
                    ->middleware('permission:form.delete');

                Route::post('/responses/bulk-delete', [FormController::class, 'bulkDelete'])
                    ->name('bulk-delete')
                    ->middleware('permission:form.delete');
            });

        Route::prefix('form-text')
            ->as('form.text.')
            ->group(function (): void {
                Route::get('/', [FormTextController::class, 'edit'])
                    ->name('edit')
                    ->middleware('permission:form.view');

                Route::put('/', [FormTextController::class, 'update'])
                    ->name('update')
                    ->middleware('permission:form.edit');
            });

        Route::prefix('form-labels')
            ->as('form.label.')
            ->group(function (): void {
                Route::get('/', [FormLabelController::class, 'index'])
                    ->name('index')
                    ->middleware('permission:form.view');

                Route::get('/create', [FormLabelController::class, 'create'])
                    ->name('create')
                    ->middleware('permission:form.create');

                Route::post('/', [FormLabelController::class, 'store'])
                    ->name('store')
                    ->middleware('permission:form.create');

                Route::get('/{label}/edit', [FormLabelController::class, 'edit'])
                    ->name('edit')
                    ->middleware('permission:form.edit');

                Route::put('/{label}', [FormLabelController::class, 'update'])
                    ->name('update')
                    ->middleware('permission:form.edit');

                Route::delete('/{label}', [FormLabelController::class, 'destroy'])
                    ->name('destroy')
                    ->middleware('permission:form.delete');

                Route::post('/update-order', [FormLabelController::class, 'updateOrder'])
                    ->name('update-order')
                    ->middleware('permission:form.edit');
            });
    });