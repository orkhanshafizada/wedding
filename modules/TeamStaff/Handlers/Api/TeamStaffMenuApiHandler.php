<?php

namespace Modules\TeamStaff\Handlers\Api;

use Illuminate\Database\Eloquent\Builder;
use Modules\Menu\Contracts\MenuTypeApiHandler;
use Modules\Menu\DTO\MenuDetailContext;
use Modules\Menu\Models\Menu;
use Modules\Menu\Services\MenuSeoService;
use Modules\TeamStaff\Http\Resources\TeamStaffResource;
use Modules\TeamStaff\Models\TeamStaff;

class TeamStaffMenuApiHandler implements MenuTypeApiHandler
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
            'items' => TeamStaffResource::collection($rows)->resolve(),
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
        $staff = $this->findByDataSlug($this->baseQuery($menu), $dataSlug);

        if (!$staff) {
            abort(404, 'Team staff not found.');
        }

        $title = $staff->name ?? null;
        $description = $staff->position ?? null;
        $image = $staff->photo_url ?? null;
        $publishedTime = $staff->created_at?->format(\DateTimeInterface::ATOM);
        $modifiedTime = $staff->updated_at?->format(\DateTimeInterface::ATOM);

        return [
            'mode' => 'detail',
            'slug' => $dataSlug,
            'item' => (new TeamStaffResource($staff))->resolve(),
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
        return TeamStaff::query()
            ->where('menu_id', $menu->id)
            ->active()
            ->ordered();
    }

    private function findByDataSlug(Builder $query, string $dataSlug): ?TeamStaff
    {
        if (ctype_digit($dataSlug)) {
            return (clone $query)->whereKey((int) $dataSlug)->first();
        }

        return null;
    }
}
