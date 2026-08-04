<?php

use Illuminate\Support\Facades\Route;
use Modules\TeamStaff\Http\Controllers\Admin\TeamStaffController;

Route::prefix('menus/{menu}/team-staff')
    ->as('team-staff.')
    ->group(function () {

        Route::get('/', [TeamStaffController::class, 'index'])
            ->name('index');

        Route::post('/update-order', [TeamStaffController::class, 'updateOrder'])
            ->name('update-order')
            ->middleware('permission:team_staff.edit');

        Route::get('/create', [TeamStaffController::class, 'create'])
            ->name('create')
            ->middleware('permission:team_staff.create');

        Route::post('/', [TeamStaffController::class, 'store'])
            ->name('store')
            ->middleware('permission:team_staff.create');

        Route::get('/{teamStaff}/edit', [TeamStaffController::class, 'edit'])
            ->name('edit')
            ->middleware('permission:team_staff.edit');

        Route::put('/{teamStaff}', [TeamStaffController::class, 'update'])
            ->name('update')
            ->middleware('permission:team_staff.edit');

        Route::delete('/{teamStaff}', [TeamStaffController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:team_staff.delete');
    });
