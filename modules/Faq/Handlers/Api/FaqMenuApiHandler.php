<?php

namespace Modules\FAQ\Handlers\Api;

use Illuminate\Database\Eloquent\Builder;
use Modules\FAQ\Http\Resources\FAQResource;
use Modules\FAQ\Models\FAQ;
use Modules\Menu\Contracts\MenuTypeApiHandler;
use Modules\Menu\DTO\MenuDetailContext;
use Modules\Menu\Models\Menu;
use Modules\Menu\Services\MenuSeoService;

class FaqMenuApiHandler implements MenuTypeApiHandler
{
    public function __construct(
        protected readonly MenuSeoService $menuSeoService
    ) {
    }

    public function handle(Menu $menu, MenuDetailContext $context): array
    {
        $dataSlug = trim((string) $context->request->query('data_slug', ''));

        if ($dataSlug !== '') {
            return $this->detail($menu, $context, $dataSlug);
        }

        return $this->list($menu, $context);
    }

    private function list(Menu $menu, MenuDetailContext $context): array
    {
        $perPage = $context->perPage(50);
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
            'items' => FAQResource::collection($rows)->resolve(),
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
            'seo' => $seo,
        ];
    }

    private function detail(Menu $menu, MenuDetailContext $context, string $dataSlug): array
    {
        $faq = $this->findFaqByDataSlug($this->baseQuery($menu), $dataSlug);

        if (!$faq) {
            abort(404, 'FAQ not found.');
        }

        $faq->incrementViewCount();

        $title = $faq->question ?? null;
        $description = $faq->answer ?? null;
        $publishedTime = $faq->created_at?->format(\DateTimeInterface::ATOM);
        $modifiedTime = $faq->updated_at?->format(\DateTimeInterface::ATOM);

        return [
            'mode' => 'detail',
            'slug' => $dataSlug,
            'item' => (new FAQResource($faq))->resolve(),
            'seo' => $this->menuSeoService->buildMenuSeo(
                menu: $menu,
                locale: $context->locale,
                itemLinksByLocale: [],
                overrides: $this->menuSeoService->resolveItemSeoDefaults(
                    menu: $menu,
                    locale: $context->locale,
                    title: $title,
                    description: $description,
                    metaTitle: $title,
                    metaDescription: $description,
                    metaKeywords: null,
                    image: null,
                    articleSection: $menu->getAttribute('api_name'),
                    publishedTime: $publishedTime,
                    modifiedTime: $modifiedTime,
                    ogType: 'article',
                    structuredType: 'FAQPage'
                )
            ),
        ];
    }

    private function baseQuery(Menu $menu): Builder
    {
        return FAQ::query()
            ->where('menu_id', $menu->id)
            ->active()
            ->ordered();
    }

    private function findFaqByDataSlug(Builder $query, string $dataSlug): ?FAQ
    {
        if (ctype_digit($dataSlug)) {
            return (clone $query)->whereKey((int) $dataSlug)->first();
        }

        return null;
    }
}
