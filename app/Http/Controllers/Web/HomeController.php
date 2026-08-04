<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\Form\Services\PublicFormService;
use Modules\Menu\Enums\MenuType;
use Modules\Menu\Models\Menu;
use Modules\Menu\Services\MenuWebPageResolver;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    public function __construct(
        private readonly MenuWebPageResolver $menuWebPageResolver,
        private readonly PublicFormService $publicFormService
    ) {
    }

    public function index(): Response|View
    {
        $this->sharePublicFormData();

        $menu = Menu::query()
            ->where('type', MenuType::CATEGORIES->value)
            ->where('status', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        if ($menu === null) {
            return view('web.index');
        }

        return $this->menuWebPageResolver->handle($menu);
    }

    private function sharePublicFormData(): void
    {
        $formData = $this->publicFormService->getActiveFormData();

        view()->share([
            'wishFormMenu' => $formData['menu'],
            'wishFormLabels' => $formData['labels'],
            'approvedWishes' => $formData['responses'],
        ]);
    }
}