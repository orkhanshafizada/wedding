<?php

use Illuminate\Support\Facades\Route;
use Modules\Gallery\Http\Controllers\Admin\GalleryAlbumController;
use Modules\Gallery\Http\Controllers\Admin\GalleryAlbumItemController;

Route::prefix('menus/{menu}/gallery')
    ->as('gallery.')
    ->group(function () {
        // Albums
        Route::get('/', [GalleryAlbumController::class, 'index'])->name('index')->middleware('permission:gallery.view');
        Route::get('/create', [GalleryAlbumController::class, 'create'])->name('create')->middleware('permission:gallery.create');
        Route::post('/', [GalleryAlbumController::class, 'store'])->name('store')->middleware('permission:gallery.create');
        Route::get('/{album}/edit', [GalleryAlbumController::class, 'edit'])->name('edit')->middleware('permission:gallery.edit');
        Route::put('/{album}', [GalleryAlbumController::class, 'update'])->name('update')->middleware('permission:gallery.edit');
        Route::delete('/{album}', [GalleryAlbumController::class, 'destroy'])->name('destroy')->middleware('permission:gallery.delete');
        Route::post('/update-order', [GalleryAlbumController::class, 'updateOrder'])->name('update-order')->middleware('permission:gallery.edit');

        // Album Items
        Route::get('/{album}/items', [GalleryAlbumItemController::class, 'index'])->name('items.index')->middleware('permission:gallery.view');
        Route::get('/{album}/items/create', [GalleryAlbumItemController::class, 'create'])->name('items.create')->middleware('permission:gallery.create');
        Route::post('/{album}/items', [GalleryAlbumItemController::class, 'store'])->name('items.store')->middleware('permission:gallery.create');
        Route::get('/{album}/items/{item}/edit', [GalleryAlbumItemController::class, 'edit'])->name('items.edit')->middleware('permission:gallery.edit');
        Route::put('/{album}/items/{item}', [GalleryAlbumItemController::class, 'update'])->name('items.update')->middleware('permission:gallery.edit');
        Route::delete('/{album}/items/{item}', [GalleryAlbumItemController::class, 'destroy'])->name('items.destroy')->middleware('permission:gallery.delete');
        Route::post('/{album}/items/update-order', [GalleryAlbumItemController::class, 'updateOrder'])->name('items.update-order')->middleware('permission:gallery.edit');
    });
