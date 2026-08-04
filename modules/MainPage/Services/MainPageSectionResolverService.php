<?php

namespace Modules\MainPage\Services;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Banner\Http\Resources\BannerResource;
use Modules\Banner\Models\Banner;
use Modules\MainPage\Enums\MainPageSectionSourceType;
use Modules\MainPage\Models\MainPageSection;
use Modules\Menu\DTO\MenuDetailContext;
use Modules\Menu\Enums\ContentType;
use Modules\Menu\Enums\MenuType;
use Modules\Menu\Http\Resources\MenuResource;
use Modules\Menu\Models\Menu;
use Modules\Menu\Services\MenuApiDetailResolver;
use Modules\Menu\Services\MenuApiHydrator;
use Modules\Menu\Services\MenuIncludedItemApiResolver;
use Modules\Product\Http\Resources\Api\Product\VariationResource;
use Modules\Product\Http\Resources\Api\ProductBlockIndexResource;
use Modules\Product\Models\Block\ProductBlock;
use Modules\Product\Models\Filter\ProductFilter;
use Modules\Product\Models\Filter\ProductFilterValue;
use Modules\Product\Models\Product;
use Modules\Product\Models\Variation\ProductVariation;
use Modules\Product\Services\ProductBlockService;
use Modules\Slider\Http\Resources\SliderResource;
use Modules\Slider\Models\Slider;

class MainPageSectionResolverService
{
    public function __construct(
        protected readonly MenuApiDetailResolver $menuApiDetailResolver,
        protected readonly MenuApiHydrator $menuApiHydrator,
        protected readonly MenuIncludedItemApiResolver $menuIncludedItemApiResolver,
        protected readonly ProductBlockService $productBlockService
    ) {
    }

    public function resolve(MainPageSection $section, Request $request): array
    {
        return match ($section->source_type) {
            MainPageSectionSourceType::SLIDER->value => $this->resolveSlider($section, $request),
            MainPageSectionSourceType::BANNER->value => $this->resolveBanner($section, $request),
            MainPageSectionSourceType::BRAND->value => $this->brandItems($section, $request),
            MainPageSectionSourceType::PRODUCT_BLOCK->value => $this->resolveProductBlock($section, $request),
            MainPageSectionSourceType::SHOW_ON_MAIN_PAGE_CATEGORIES->value => $this->resolveShowOnMainPageCategories($request),
            MainPageSectionSourceType::SHOW_ON_MAIN_PAGE_SERVICES->value => $this->resolveShowOnMainPageServices($request),
            MainPageSectionSourceType::MENU_TYPE->value => $this->resolveSelectedMenuTypeMenu($section, $request),
            default => [
                'items' => [],
            ],
        };
    }

    private function resolveSlider(MainPageSection $section, Request $request): array
    {
        $query = Slider::query()
            ->with('translations')
            ->active()
            ->ordered();

        if ($section->limit !== null) {
            $query->limit((int) $section->limit);
        }

        $items = $query->get();

        return [
            'items' => SliderResource::collection($items)->toArray($request),
        ];
    }

    private function resolveBanner(MainPageSection $section, Request $request): array
    {
        $position = (string) $section->source_reference;

        $query = Banner::query()
            ->with('translations')
            ->active()
            ->ordered()
            ->where('position', $position);

        if ($section->limit !== null) {
            $query->limit((int) $section->limit);
        }

        $items = $query->get();

        return [
            'position' => $position,
            'position_name' => $items->first()?->position_name,
            'items' => BannerResource::collection($items)->toArray($request),
        ];
    }

    private function resolveProductBlock(MainPageSection $section, Request $request): array
    {
        $block = ProductBlock::query()
            ->with([
                'translations',
                'selectedCategories.translations',
                'selectedBrands.translations',
                'selectedProducts.translations',
            ])
            ->active()
            ->find((int) $section->source_reference);

        if (!$block) {
            return [
                'block' => null,
                'items' => [],
            ];
        }

        $languageId = Language::query()
            ->where('code', app()->getLocale())
            ->value('id');

        $limit = $section->limit !== null
            ? (int) $section->limit
            : (int) $block->limit;

        $items = $this->resolveProductBlockItems(
            $block,
            $limit > 0 ? $limit : null
        )
            ->map(function (ProductVariation $variation) use ($languageId): array {
                return $this->mapVariationForResource(
                    $variation,
                    $languageId !== null ? (int) $languageId : null
                );
            })
            ->values()
            ->all();

        $blockTranslation = $block->translations->firstWhere(
            'language_id',
            (int) $languageId
        ) ?? $block->translations->first();

        return [
            'block' => [
                'id' => (int) $block->id,
                'title' => (string) ($blockTranslation?->title ?? ('#' . $block->id)),
                'limit' => (int) $block->limit,
                'category_scope' => (string) $block->category_scope,
                'brand_scope' => (string) $block->brand_scope,
                'product_scope' => (string) $block->product_scope,
                'only_discount_products' => (bool) $block->only_discount_products,
                'only_new_products' => (bool) $block->only_new_products,
                'best_seller_products' => (bool) $block->best_seller_products,
            ],
            'items' => VariationResource::collection($items)->toArray($request),
        ];
    }

    private function resolveShowOnMainPageCategories(Request $request): array
    {
        $menus = Menu::query()
            ->with('translations')
            ->active()
            ->where('show_on_main_page', true)
            ->where('type', MenuType::CATEGORIES->value)
            ->get();

        $hierarchyIndex = $this->loadMenuHierarchyIndex($menus);

        $sortedMenus = $menus
            ->sort(function (Menu $firstMenu, Menu $secondMenu) use (
                $hierarchyIndex
            ): int {
                return $this->resolveMenuHierarchySortKey(
                        $firstMenu,
                        $hierarchyIndex
                    ) <=> $this->resolveMenuHierarchySortKey(
                        $secondMenu,
                        $hierarchyIndex
                    );
            })
            ->values();

        return [
            'items' => $sortedMenus
                ->map(function (Menu $menu) use ($request): array {
                    return $this->buildResolvedMenuTree($menu, $request);
                })
                ->all(),
        ];
    }

    private function loadMenuHierarchyIndex(Collection $menus): Collection
    {
        $hierarchyIndex = $menus
            ->mapWithKeys(static function (Menu $menu): array {
                return [
                    (int) $menu->id => [
                        'id' => (int) $menu->id,
                        'parent_id' => $menu->parent_id !== null
                            ? (int) $menu->parent_id
                            : null,
                        'sort_order' => (int) $menu->sort_order,
                    ],
                ];
            });

        $pendingParentIds = $hierarchyIndex
            ->pluck('parent_id')
            ->filter(static fn ($parentId): bool => $parentId !== null)
            ->map(static fn ($parentId): int => (int) $parentId)
            ->unique()
            ->values();

        while ($pendingParentIds->isNotEmpty()) {
            $missingParentIds = $pendingParentIds
                ->reject(
                    static fn (int $parentId): bool => $hierarchyIndex->has($parentId)
                )
                ->values();

            if ($missingParentIds->isEmpty()) {
                break;
            }

            $parents = Menu::query()
                ->whereKey($missingParentIds->all())
                ->get([
                    'id',
                    'parent_id',
                    'sort_order',
                ]);

            if ($parents->isEmpty()) {
                break;
            }

            foreach ($parents as $parent) {
                $hierarchyIndex->put(
                    (int) $parent->id,
                    [
                        'id' => (int) $parent->id,
                        'parent_id' => $parent->parent_id !== null
                            ? (int) $parent->parent_id
                            : null,
                        'sort_order' => (int) $parent->sort_order,
                    ]
                );
            }

            $pendingParentIds = $parents
                ->pluck('parent_id')
                ->filter(static fn ($parentId): bool => $parentId !== null)
                ->map(static fn ($parentId): int => (int) $parentId)
                ->unique()
                ->values();
        }

        return $hierarchyIndex;
    }

    private function resolveMenuHierarchySortKey(
        Menu $menu,
        Collection $hierarchyIndex
    ): array {
        $hierarchyPath = [];
        $visitedMenuIds = [];
        $currentMenuId = (int) $menu->id;

        while (
            $currentMenuId > 0
            && !in_array($currentMenuId, $visitedMenuIds, true)
        ) {
            $visitedMenuIds[] = $currentMenuId;

            $hierarchyItem = $hierarchyIndex->get($currentMenuId);

            if (!is_array($hierarchyItem)) {
                break;
            }

            array_unshift(
                $hierarchyPath,
                (int) $hierarchyItem['id']
            );

            array_unshift(
                $hierarchyPath,
                (int) $hierarchyItem['sort_order']
            );

            $parentId = $hierarchyItem['parent_id'];

            if ($parentId === null) {
                break;
            }

            $currentMenuId = (int) $parentId;
        }

        return [
            $menu->parent_id === null ? 0 : 1,
            ...$hierarchyPath,
        ];
    }

    private function resolveShowOnMainPageServices(Request $request): array
    {
        $menus = Menu::query()
            ->with([
                'translations',
                'childrenRecursive.translations',
            ])
            ->active()
            ->where('show_on_main_page', true)
            ->where('type', MenuType::CONTENT->value)
            ->where(function ($query): void {
                $query
                    ->where('view_type', ContentType::SERVICES->value)
                    ->orWhereRaw(
                        'LOWER(view_type) = ?',
                        [mb_strtolower(ContentType::SERVICES->value)]
                    );
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return [
            'items' => $menus
                ->map(function (Menu $menu) use ($request): array {
                    return $this->buildResolvedMenuTree($menu, $request);
                })
                ->all(),
        ];
    }

    private function resolveSelectedMenuTypeMenu(
        MainPageSection $section,
        Request $request
    ): array {
        $query = Menu::query()
            ->with([
                'translations',
                'childrenRecursive.translations',
                'includedItems',
            ])
            ->active()
            ->where('show_on_main_page', true)
            ->where('type', (string) $section->menu_type)
            ->whereKey((int) $section->source_reference);

        if (!empty($section->menu_view_type)) {
            $query->where(
                'view_type',
                (string) $section->menu_view_type
            );
        }

        $menu = $query->first();

        if (!$menu) {
            return [
                'menu' => null,
                'data' => null,
                'children' => [],
                'included' => [],
            ];
        }

        return $this->buildResolvedMenuTree($menu, $request);
    }

    private function buildResolvedMenuTree(
        Menu $menu,
        Request $request,
        array $visitedMenuIds = []
    ): array {
        $menuId = (int) $menu->id;

        if (in_array($menuId, $visitedMenuIds, true)) {
            return [
                'menu' => (new MenuResource($menu, false, false))->resolve($request),
                'data' => null,
                'children' => [],
                'included' => [],
            ];
        }

        $visitedMenuIds[] = $menuId;

        $this->loadMenuRelations($menu);

        $locale = app()->getLocale();
        $fallbackLocale = (string) config('app.locale');

        $this->menuApiHydrator->hydrate(
            $menu,
            $locale,
            $fallbackLocale
        );

        $context = new MenuDetailContext(
            request: $request,
            locale: $locale,
            fallbackLocale: $fallbackLocale
        );

        $data = $this->resolveMenuData($menu, $context);

        $children = collect($menu->childrenRecursive ?? [])
            ->filter(function (Menu $child): bool {
                return (bool) $child->show_on_main_page;
            })
            ->sortBy([
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->values()
            ->map(function (Menu $child) use (
                $request,
                $visitedMenuIds
            ): array {
                return $this->buildResolvedMenuTree(
                    $child,
                    $request,
                    $visitedMenuIds
                );
            })
            ->all();

        $included = $this->menuIncludedItemApiResolver->resolveForMenu(
            $menu,
            $locale,
            $fallbackLocale,
            $visitedMenuIds,
            max(count($visitedMenuIds) - 1, 0),
            $request
        );

        return [
            'menu' => (new MenuResource($menu, false, false))->resolve($request),
            'data' => $data,
            'children' => $children,
            'included' => $included,
        ];
    }

    private function loadMenuRelations(Menu $menu): void
    {
        $relations = [
            'translations',
            'childrenRecursive.translations',
        ];

        if ($menu->show_on_main_page) {
            $relations[] = 'includedItems';
        }

        $menu->loadMissing($relations);
    }

    private function resolveMenuData(
        Menu $menu,
        MenuDetailContext $context
    ): mixed {
        try {
            return $this->menuApiDetailResolver->handle(
                $menu,
                $context
            );
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolveProductBlockItems(
        ProductBlock $block,
        ?int $limit = null
    ): Collection {
        $originalLimit = (int) $block->limit;

        if ($limit !== null && $limit > 0) {
            $block->limit = $limit;
        }

        $items = $this->productBlockService->getBlockProducts($block);

        $block->limit = $originalLimit;

        return $items;
    }

    private function expandMenuIdsWithDescendants(array $menuIds): array
    {
        $resolvedIds = collect($menuIds)
            ->map(static fn ($menuId): int => (int) $menuId)
            ->filter(static fn (int $menuId): bool => $menuId > 0)
            ->unique()
            ->values()
            ->all();

        $queue = $resolvedIds;

        while ($queue !== []) {
            $childIds = Menu::query()
                ->whereIn('parent_id', $queue)
                ->pluck('id')
                ->map(static fn ($menuId): int => (int) $menuId)
                ->values()
                ->all();

            $newIds = array_values(
                array_diff($childIds, $resolvedIds)
            );

            if ($newIds === []) {
                break;
            }

            $resolvedIds = array_values(
                array_unique(
                    array_merge($resolvedIds, $newIds)
                )
            );

            $queue = $newIds;
        }

        return $resolvedIds;
    }

    private function brandItems(
        MainPageSection $section,
        Request $request
    ): array {
        $filter = $this->brandFilter();

        if (!$filter) {
            return [
                'filter' => null,
                'items' => [],
            ];
        }

        $languageId = Language::query()
            ->where('code', app()->getLocale())
            ->value('id');

        $limit = $section->limit !== null
            ? (int) $section->limit
            : null;

        $values = $filter->values()
            ->with('translations')
            ->active()
            ->showOnMain()
            ->ordered()
            ->when(
                $limit !== null && $limit > 0,
                static function ($query) use ($limit): void {
                    $query->limit($limit);
                }
            )
            ->get()
            ->map(function (ProductFilterValue $value) use ($languageId): array {
                $translation = $value->translations->firstWhere(
                    'language_id',
                    (int) $languageId
                ) ?? $value->translations->first();

                return [
                    'value_id' => (int) $value->id,
                    'name' => (string) ($translation?->name ?? ('#' . $value->id)),
                    'slug' => (string) ($translation?->slug ?? ''),
                    'count' => 0,
                    'color' => $value->color !== null
                        ? (string) $value->color
                        : null,
                    'image' => $value->image
                        ? asset('storage/' . $value->image)
                        : null,
                    'meta_title' => $translation?->meta_title,
                    'meta_description' => $translation?->meta_description,
                    'meta_keywords' => $translation?->meta_keywords,
                ];
            })
            ->values()
            ->all();

        $filterTranslation = $filter->translations->firstWhere(
            'language_id',
            (int) $languageId
        ) ?? $filter->translations->first();

        return [
            'filter' => [
                'filter_id' => (int) $filter->id,
                'name' => (string) ($filterTranslation?->name ?? 'Brand'),
                'slug' => (string) ($filterTranslation?->slug ?? 'brand'),
            ],
            'items' => $values,
        ];
    }

    private function brandFilter(): ?ProductFilter
    {
        $keywords = [
            'brand',
            'brands',
            'brend',
            'brendler',
            'бренд',
            'бренды',
            'брендлер',
            'марка',
            'марки',
        ];

        $normalizedKeywords = collect($keywords)
            ->map(static fn (string $keyword): string => mb_strtolower(trim($keyword)))
            ->filter(static fn (string $keyword): bool => $keyword !== '')
            ->unique()
            ->values()
            ->all();

        return ProductFilter::query()
            ->with('translations')
            ->active()
            ->whereHas(
                'translations',
                function ($query) use ($normalizedKeywords): void {
                    $query->where(
                        function ($query) use ($normalizedKeywords): void {
                            foreach ($normalizedKeywords as $keyword) {
                                $query
                                    ->orWhereRaw(
                                        'LOWER(name) = ?',
                                        [$keyword]
                                    )
                                    ->orWhereRaw(
                                        'LOWER(slug) = ?',
                                        [$keyword]
                                    )
                                    ->orWhereRaw(
                                        'LOWER(name) LIKE ?',
                                        ['%' . $keyword . '%']
                                    )
                                    ->orWhereRaw(
                                        'LOWER(slug) LIKE ?',
                                        ['%' . $keyword . '%']
                                    );
                            }
                        }
                    );
                }
            )
            ->ordered()
            ->get()
            ->sortBy(function (ProductFilter $filter) use (
                $normalizedKeywords
            ): array {
                $translationValues = $filter->translations
                    ->flatMap(static fn ($translation): array => [
                        mb_strtolower(trim((string) $translation->name)),
                        mb_strtolower(trim((string) $translation->slug)),
                    ])
                    ->filter(static fn (string $value): bool => $value !== '')
                    ->values();

                foreach ($translationValues as $value) {
                    if (in_array($value, $normalizedKeywords, true)) {
                        return [
                            0,
                            (int) $filter->sort_order,
                            (int) $filter->id,
                        ];
                    }
                }

                foreach ($translationValues as $value) {
                    foreach ($normalizedKeywords as $keyword) {
                        if (str_contains($value, $keyword)) {
                            return [
                                1,
                                (int) $filter->sort_order,
                                (int) $filter->id,
                            ];
                        }
                    }
                }

                return [
                    2,
                    (int) $filter->sort_order,
                    (int) $filter->id,
                ];
            })
            ->first();
    }

    private function mapVariationForResource(
        ProductVariation $variation,
        ?int $languageId = null
    ): array {
        $translation = $languageId !== null
            ? $variation->translations->firstWhere(
                'language_id',
                $languageId
            )
            : null;

        $translation = $translation
            ?? $variation->translations->first();

        $mainMedia = $variation->media->firstWhere('is_main', true)
            ?? $variation->media->first();

        return [
            'variation_id' => (int) $variation->id,
            'variation_uuid' => (string) $variation->uuid,
            'product_id' => (int) $variation->product_id,
            'name' => (string) ($translation?->name ?? ('#' . $variation->id)),
            'slug' => (string) ($translation?->slug ?? ''),
            'stock' => (int) $variation->stock,
            'price' => $variation->price !== null
                ? (float) $variation->price
                : 0,
            'old_price' => $variation->old_price !== null
                ? (float) $variation->old_price
                : null,
            'discount_price' => $variation->discount_price !== null
                ? (float) $variation->discount_price
                : null,
            'meta_title' => $translation?->meta_title,
            'meta_description' => $translation?->meta_description,
            'meta_keywords' => $translation?->meta_keywords,
            'main_image_path' => $mainMedia?->path,
            'gallery' => $variation->media
                ->map(static fn ($media): array => [
                    'id' => (int) $media->id,
                    'path' => (string) $media->path,
                    'url' => (string) $media->url,
                    'is_main' => (bool) $media->is_main,
                    'sort_order' => (int) $media->sort_order,
                ])
                ->values()
                ->all(),
            'filters' => [],
        ];
    }
}
