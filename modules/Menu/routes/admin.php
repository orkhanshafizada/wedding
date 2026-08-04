<?php

use Illuminate\Support\Facades\Route;
use Modules\Menu\Http\Controllers\Admin\MenuAjaxController;
use Modules\Menu\Http\Controllers\Admin\MenuController;
use Modules\Menu\Http\Controllers\Admin\MenuContentController;
use Modules\Menu\Http\Controllers\Admin\MenuRouteController;

Route::prefix('menus')
    ->as('menus.')
    ->group(function (): void {
        Route::get('/', [MenuController::class, 'index'])
            ->middleware('permission:menu.view')
            ->name('index');

        Route::get('/create', [MenuController::class, 'create'])
            ->middleware('permission:menu.create')
            ->name('create');

        Route::post('/', [MenuController::class, 'store'])
            ->middleware('permission:menu.create')
            ->name('store');

        Route::get('/ajax/fontawesome-icons', [MenuAjaxController::class, 'fontAwesomeIcons'])
            ->middleware('permission:menu.create')
            ->name('ajax.fontawesome-icons');

        Route::post('/reorder', [MenuController::class, 'reorder'])
            ->middleware('permission:menu.create')
            ->name('reorder');

        Route::get('/{menu}/edit', [MenuController::class, 'edit'])
            ->middleware('permission:menu.edit,menu')
            ->name('edit');

        Route::put('/{menu}', [MenuController::class, 'update'])
            ->middleware('permission:menu.edit,menu')
            ->name('update');

        Route::patch('/{menu}/toggle', [MenuController::class, 'toggle'])
            ->middleware('permission:menu.edit,menu')
            ->name('toggle');

        Route::get('/{menu}/route', MenuRouteController::class)
            ->middleware('permission:menu.content,menu')
            ->name('route');

        Route::get('/{menu}/content', [MenuContentController::class, 'edit'])
            ->middleware('permission:menu.content,menu')
            ->name('content.edit');

        Route::put('/{menu}/content', [MenuContentController::class, 'update'])
            ->middleware('permission:menu.content,menu')
            ->name('content.update');

        Route::post('/{menu}/content/files', [MenuContentController::class, 'uploadFiles'])
            ->middleware('permission:menu.content,menu')
            ->name('content.files.upload');

        Route::delete('/{menu}/content/files/{file}', [MenuContentController::class, 'deleteFile'])
            ->middleware('permission:menu.content,menu')
            ->name('content.files.delete');

        Route::patch('/{menu}/content/files/reorder', [MenuContentController::class, 'reorderFiles'])
            ->middleware('permission:menu.content,menu')
            ->name('content.files.reorder');

        Route::delete('/{menu}', [MenuController::class, 'destroy'])
            ->middleware('permission:menu.delete,menu')
            ->name('destroy');
    });
