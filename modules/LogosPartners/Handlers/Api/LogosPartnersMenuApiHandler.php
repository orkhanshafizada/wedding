<?php

namespace Modules\LogosPartners\Handlers\Api;

use Illuminate\Database\Eloquent\Builder;
use Modules\LogosPartners\Http\Resources\LogosPartnerResource;
use Modules\LogosPartners\Models\LogosPartner;
use Modules\Menu\Contracts\MenuTypeApiHandler;
use Modules\Menu\DTO\MenuDetailContext;
use Modules\Menu\Models\Menu;
use Modules\Menu\Services\MenuSeoService;

class LogosPartnersMenuApiHandler implements MenuTypeApiHandler
{
    public function __construct(
        protected readonly MenuSeoService $menuSeoService
    ) {
    }

    public function handle(Menu $menu, MenuDetailContext $context): array
    {
        $dataSlug = trim((string) $context->request->query('data_slug', ''));

        if ($dataSlug !== '') {
            return $this->detailBySlug($menu, $context, $dataSlug);
        }

        return $this->list($menu, $context);
    }

    private function list(Menu $menu, MenuDetailContext $context): array
    {
        $perPage = $context->perPage(24);
        $page = $context->page(1);

        $baseQuery = $this->baseQuery($menu);
        $total = (int) (clone $baseQuery)->count();

        $rows = (clone $baseQuery)
            ->forPage($page, $perPage)
            ->get();

        $lastPage = (int) ceil($total / max(1, $perPage));

        $seo = $this->menuSeoService->buildMenuSeo(
            menu: $menu,
            locale: $context->locale,
            query: $page > 1 ? ['page' => $page] : []
        );

        $seo = $this->menuSeoService->appendPaginationSeo(
            seo: $seo,
            menu: $menu,
            locale: $context->locale,
            page: $page,
            lastPage: $lastPage
        );

        return [
            'mode' => 'list',
            'items' => LogosPartnerResource::collection($rows)->resolve(),
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
            'seo' => $seo,
        ];
    }

    private function detailBySlug(Menu $menu, MenuDetailContext $context, string $dataSlug): array
    {
        $query = $this->baseQuery($menu);

        $partner = $this->findPartnerByDataSlug($query, $context, $dataSlug);

        if (!$partner) {
            abort(404, 'Logos partner not found.');
        }

        $title = $partner->name ?? null;
        $description = $partner->description ?? null;
        $image = $partner->image_url ?? null;
        $publishedTime = $partner->created_at?->format(\DateTimeInterface::ATOM);
        $modifiedTime = $partner->updated_at?->format(\DateTimeInterface::ATOM);

        return [
            'mode' => 'detail',
            'slug' => $dataSlug,
            'item' => (new LogosPartnerResource($partner))->resolve(),
            'seo' => $this->menuSeoService->buildMenuSeo(
                menu: $menu,
                locale: $context->locale,
                overrides: $this->menuSeoService->resolveItemSeoDefaults(
                    menu: $menu,
                    locale: $context->locale,
                    title: $title,
                    description: $description,
                    metaTitle: $title,
                    metaDescription: $description,
                    metaKeywords: null,
                    image: $image,
                    articleSection: $menu->getAttribute('api_name'),
                    publishedTime: $publishedTime,
                    modifiedTime: $modifiedTime
                )
            ),
        ];
    }

    private function baseQuery(Menu $menu): Builder
    {
        return LogosPartner::query()
            ->where('menu_id', $menu->id)
            ->active()
            ->ordered();
    }

    private function findPartnerByDataSlug(Builder $query, MenuDetailContext $context, string $dataSlug): ?LogosPartner
    {
        $locale = $context->locale;
        $fallbackLocale = $context->fallbackLocale;

        $byLocale = (clone $query)->where("slug->{$locale}", $dataSlug)->first();
        if ($byLocale) {
            return $byLocale;
        }

        if ($fallbackLocale !== $locale) {
            $byFallback = (clone $query)->where("slug->{$fallbackLocale}", $dataSlug)->first();
            if ($byFallback) {
                return $byFallback;
            }
        }

        if (ctype_digit($dataSlug)) {
            return (clone $query)->whereKey((int) $dataSlug)->first();
        }

        return null;
    }
}
