<?php

use Illuminate\Support\Facades\Route;
use Modules\Gallery\Http\Controllers\Api\GalleryApiController;

// Routes will be automatically prefixed with 'api/v1/gallery' by main routes/api.php
Route::get('/{menuId}', [GalleryApiController::class, 'index'])
    ->whereNumber('menuId')
    ->name('gallery.index');

Route::get('/{menuId}/items', [GalleryApiController::class, 'itemsByMenu'])
    ->whereNumber('menuId')
    ->name('gallery.items.menu');

Route::get('/{menuId}/albums/{albumId}', [GalleryApiController::class, 'showAlbum'])
    ->whereNumber('menuId')
    ->whereNumber('albumId')
    ->name('gallery.albums.show');

Route::get('/{menuId}/albums/{albumId}/items', [GalleryApiController::class, 'itemsByAlbum'])
    ->whereNumber('menuId')
    ->whereNumber('albumId')
    ->name('gallery.items.album');
