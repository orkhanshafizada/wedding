<?php

namespace Modules\Form\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use Modules\Form\Http\Requests\StoreFormResponseRequest;
use Modules\Form\Services\FormResponseService;
use Modules\Menu\Enums\MenuType;
use Modules\Menu\Models\Menu;
use Symfony\Component\HttpFoundation\Response;

class FormResponseController extends BaseApiController
{
    public function __construct(
        private readonly FormResponseService $service
    ) {
    }

    public function store(
        StoreFormResponseRequest $request,
        Menu $menu
    ): JsonResponse {
        $menuType = $menu->type instanceof MenuType
            ? $menu->type->value
            : (string) $menu->type;

        abort_unless(
            $menuType === MenuType::FORM->value && (int) $menu->status === 1,
            Response::HTTP_NOT_FOUND
        );

        $response = $this->service->storeFromApi(
            $menu,
            (array) $request->validated('answers')
        );

        return $this->response([
            'id' => (int) $response->id,
            'ok' => true,
            'status' => (int) $response->status,
        ], 'Your response has been submitted successfully.');
    }
}