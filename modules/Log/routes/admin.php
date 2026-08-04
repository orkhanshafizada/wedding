<?php
use Illuminate\Support\Facades\Route;
use Modules\Log\Http\Controllers\Admin\AdminSessionController;
use Modules\Log\Http\Controllers\Admin\ActivityLogController;


Route::prefix('log')
    ->as('log.')
    ->group(function () {
    Route::get('sessions', [AdminSessionController::class, 'index'])->name('sessions.index')->middleware('permission:log.session.view');
    Route::get('sessions/{session}', [AdminSessionController::class, 'show'])->name('sessions.show')->middleware('permission:log.session.view');

    Route::get('activity', [ActivityLogController::class, 'index'])->name('activity.index')->middleware('permission:log.view');
    Route::get('activity/{activity}', [ActivityLogController::class, 'show'])->name('activity.show')->middleware('permission:log.view');
});
