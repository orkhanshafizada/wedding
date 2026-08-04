<?php

namespace Modules\Menu\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use LogicException;
use Modules\Menu\DTO\MenuDetailContext;
use Modules\Menu\Http\Requests\Api\Menu\ShowMenuDetailRequest;
use Modules\Menu\Http\Resources\MenuResource;
use Modules\Menu\Services\MenuApiDetailResolver;
use Modules\Menu\Services\MenuApiHydrator;
use Modules\Menu\Services\MenuIncludedItemApiResolver;
use Modules\Menu\Services\MenuLinkResolver;

class MenuDetailController extends BaseApiController
{
    public function __construct(
        protected readonly MenuLinkResolver $menuLinkResolver,
        protected readonly MenuApiDetailResolver $menuApiDetailResolver,
        protected readonly MenuApiHydrator $menuApiHydrator,
        protected readonly MenuIncludedItemApiResolver $menuIncludedItemApiResolver
    ) {
    }

    public function show(ShowMenuDetailRequest $request): JsonResponse
    {
        $menu = $this->menuLinkResolver->findByLinkOrFail((string) $request->query('link'));

        $locale = trim((string) $request->query('locale', ''));
        if ($locale === '') {
            $locale = app()->getLocale();
        }

        app()->setLocale($locale);

        $fallbackLocale = (string) config('app.locale');

        $this->menuApiHydrator->hydrate($menu, $locale, $fallbackLocale);
        $this->menuIncludedItemApiResolver->attachToMenu($menu, $locale, $fallbackLocale, $request);

        $context = new MenuDetailContext(
            request: $request,
            locale: $locale,
            fallbackLocale: $fallbackLocale
        );

        try {
            $payload = $this->menuApiDetailResolver->handle($menu, $context);
        } catch (LogicException) {
            $payload = null;
        }

        return $this->response([
            'type' => $menu->getAttribute('api_type'),
            'menu' => (new MenuResource($menu, false))->resolve($request),
            'data' => $payload,
            'included_items' => $menu->getAttribute('api_included_items') ?? [],
        ], 'OK');
    }
}
