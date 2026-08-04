<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminPermission\Http\Controllers\Admin\AdminPermissionController;
use Modules\AdminPermission\Http\Controllers\Admin\AdminRoleController;
use Modules\AdminPermission\Http\Controllers\Admin\AdminUserController;

Route::prefix('admins')
    ->as('admins.')
    ->group(function (): void {
        Route::get('/', [AdminUserController::class, 'index'])
            ->middleware('permission:admin.view')
            ->name('index');

        Route::get('/create', [AdminUserController::class, 'create'])
            ->middleware('permission:admin.create')
            ->name('create');

        Route::post('/', [AdminUserController::class, 'store'])
            ->middleware('permission:admin.create')
            ->name('store');

        Route::get('/{admin}/edit', [AdminUserController::class, 'edit'])
            ->whereNumber('admin')
            ->middleware('permission:admin.edit')
            ->name('edit');

        Route::put('/{admin}', [AdminUserController::class, 'update'])
            ->whereNumber('admin')
            ->middleware('permission:admin.edit')
            ->name('update');

        Route::patch('/{admin}/toggle-status', [AdminUserController::class, 'toggleStatus'])
            ->whereNumber('admin')
            ->middleware('permission:admin.edit')
            ->name('toggle-status');

        Route::delete('/{admin}', [AdminUserController::class, 'destroy'])
            ->whereNumber('admin')
            ->middleware('permission:admin.delete')
            ->name('destroy');
    });

Route::prefix('roles')
    ->as('roles.')
    ->group(function (): void {
        Route::get('/', [AdminRoleController::class, 'index'])
            ->middleware('permission:role.view')
            ->name('index');

        Route::get('/create', [AdminRoleController::class, 'create'])
            ->middleware('permission:role.create')
            ->name('create');

        Route::post('/', [AdminRoleController::class, 'store'])
            ->middleware('permission:role.create')
            ->name('store');

        Route::get('/{role}/edit', [AdminRoleController::class, 'edit'])
            ->whereNumber('role')
            ->middleware('permission:role.edit')
            ->name('edit');

        Route::put('/{role}', [AdminRoleController::class, 'update'])
            ->whereNumber('role')
            ->middleware('permission:role.edit')
            ->name('update');

        Route::delete('/{role}', [AdminRoleController::class, 'destroy'])
            ->whereNumber('role')
            ->middleware('permission:role.delete')
            ->name('destroy');
    });

Route::prefix('permissions')
    ->as('permissions.')
    ->group(function (): void {
        Route::get('/', [AdminPermissionController::class, 'index'])
            ->middleware('permission:permission.view')
            ->name('index');

        Route::get('/create', [AdminPermissionController::class, 'create'])
            ->middleware('permission:permission.create')
            ->name('create');

        Route::post('/', [AdminPermissionController::class, 'store'])
            ->middleware('permission:permission.create')
            ->name('store');

        Route::post('/sync-system', [AdminPermissionController::class, 'syncSystem'])
            ->middleware('permission:permission.create')
            ->name('sync-system');

        Route::post('/sync-menus', [AdminPermissionController::class, 'syncMenus'])
            ->middleware('permission:permission.create')
            ->name('sync-menus');

        Route::get('/{permission}/edit', [AdminPermissionController::class, 'edit'])
            ->whereNumber('permission')
            ->middleware('permission:permission.edit')
            ->name('edit');

        Route::put('/{permission}', [AdminPermissionController::class, 'update'])
            ->whereNumber('permission')
            ->middleware('permission:permission.edit')
            ->name('update');

        Route::delete('/{permission}', [AdminPermissionController::class, 'destroy'])
            ->whereNumber('permission')
            ->middleware('permission:permission.delete')
            ->name('destroy');
    });
