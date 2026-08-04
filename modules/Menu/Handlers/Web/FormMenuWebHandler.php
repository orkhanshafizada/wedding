<?php

namespace Modules\Menu\Handlers\Web;

use Illuminate\Contracts\View\View;
use Modules\Form\Services\PublicFormService;
use Modules\Menu\Contracts\MenuTypeWebHandler;
use Modules\Menu\Enums\MenuType;
use Modules\Menu\Models\Menu;
use Symfony\Component\HttpFoundation\Response;

class FormMenuWebHandler implements MenuTypeWebHandler
{
    public function __construct(
        private readonly PublicFormService $publicFormService
    ) {
    }

    public function handle(Menu $menu): Response|View
    {
        $menuType = $menu->type instanceof MenuType
            ? $menu->type->value
            : (string) $menu->type;

        abort_unless(
            $menuType === MenuType::FORM->value && (int) $menu->status === 1,
            Response::HTTP_NOT_FOUND
        );

        $formData = $this->publicFormService->getActiveFormData($menu);

        return view('web.index', [
            'wishFormMenu' => $formData['menu'],
            'wishFormLabels' => $formData['labels'],
            'approvedWishes' => $formData['responses'],
        ]);
    }
}