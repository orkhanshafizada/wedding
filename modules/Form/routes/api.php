<?php

use Illuminate\Support\Facades\Route;
use Modules\Form\Http\Controllers\Api\FormResponseController;
use Modules\Menu\Enums\MenuType;
use Modules\Menu\Models\Menu;

Route::as('forms.')
    ->prefix('forms')
    ->group(function (): void {
        Route::bind('menu', static function (string $value): Menu {
            return Menu::query()
                ->where('status', 1)
                ->where('type', MenuType::FORM->value)
                ->where(function ($query) use ($value): void {
                    $query->where('uuid', $value);

                    if (ctype_digit($value)) {
                        $query->orWhere('id', (int) $value);
                    }
                })
                ->firstOrFail();
        });

        Route::post('/menus/{menu}/responses', [FormResponseController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('menus.responses.store');
    });