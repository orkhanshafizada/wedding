<?php

namespace Modules\Grids\Handlers\Api;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Grids\Models\Grid;
use Modules\Grids\Services\GridMenuHierarchyService;
use Modules\Menu\Contracts\MenuTypeApiHandler;
use Modules\Menu\DTO\MenuDetailContext;
use Modules\Menu\Http\Resources\MenuResource;
use Modules\Menu\Models\Menu;
use Modules\Menu\Services\MenuApiHydrator;
use Modules\Menu\Services\MenuSeoService;
use Modules\Menu\Support\LocalePicker;
use Illuminate\Support\Facades\Storage;

class GridsMenuApiHandler implements MenuTypeApiHandler
{
    private const DEFAULT_PER_PAGE = 12;
    private const RELATED_PRODUCTS_LIMIT = 20;

    public function __construct(
        protected readonly MenuSeoService $menuSeoService,
        protected readonly MenuApiHydrator $menuApiHydrator,
        protected readonly GridMenuHierarchyService $gridMenuHierarchyService
    ) {
    }

    public function handle(Menu $menu, MenuDetailContext $context): array
    {
        $gridMenus = $this->gridMenuHierarchyService->resolve($menu);

        $this->hydrateGridMenus($gridMenus, $context);

        $dataSlug = trim((string) $context->request->query('data_slug', ''));

        if ($dataSlug !== '') {
            return $this->detailBySlug(
                menu: $menu,
                context: $context,
                dataSlug: $dataSlug,
                gridMenus: $gridMenus
            );
        }

        return $this->list(
            menu: $menu,
            context: $context,
            gridMenus: $gridMenus
        );
    }

    private function list(
        Menu $menu,
        MenuDetailContext $context,
        EloquentCollection $gridMenus
    ): array {
        $perPage = $context->perPage(self::DEFAULT_PER_PAGE);
        $page = $context->page(1);

        $baseQuery = $this->baseQuery(
            $this->resolveMenuIds($gridMenus)
        );

        $total = (int) (clone $baseQuery)->count();

        $rows = (clone $baseQuery)
            ->with($this->relations())
            ->forPage($page, $perPage)
            ->get();

        $gridMenusById = $gridMenus->keyBy(
            fn (Menu $gridMenu): int => (int) $gridMenu->getKey()
        );

        $items = $rows
            ->map(function (Grid $grid) use ($context, $gridMenusById): array {
                $gridMenu = $gridMenusById->get((int) $grid->menu_id);

                return $this->mapGrid(
                    grid: $grid,
                    context: $context,
                    gridMenu: $gridMenu instanceof Menu ? $gridMenu : null
                );
            })
            ->values()
            ->all();

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
            'categories' => $this->mapCategories(
                gridMenus: $gridMenus,
                rootMenu: $menu,
                context: $context
            ),
            'items' => $items,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
            'seo' => $seo,
        ];
    }

    private function detailBySlug(
        Menu $menu,
        MenuDetailContext $context,
        string $dataSlug,
        EloquentCollection $gridMenus
    ): array {
        $query = $this->baseQuery(
            $this->resolveMenuIds($gridMenus)
        )->with($this->relations());

        $grid = $this->findGridByDataSlug(
            query: $query,
            context: $context,
            dataSlug: $dataSlug
        );

        if ($grid === null) {
            abort(404, 'Grid item not found.');
        }

        $gridMenu = $gridMenus->first(
            fn (Menu $candidateMenu): bool => (int) $candidateMenu->getKey() === (int) $grid->menu_id
        );

        $title = $this->localizedValue($grid->name, $context);
        $description = $this->localizedValue($grid->content, $context);
        $metaTitle = $this->localizedValue($grid->meta_title, $context);
        $metaDescription = $this->localizedValue($grid->meta_description, $context);
        $metaKeywords = $this->localizedValue($grid->meta_keywords, $context);
        $image = $grid->main_image_url ?? $grid->banner_url;

        $publishedTime = $grid->datetime1?->format(\DateTimeInterface::ATOM)
            ?? $grid->created_at?->format(\DateTimeInterface::ATOM);

        $modifiedTime = $grid->updated_at?->format(\DateTimeInterface::ATOM)
            ?? $grid->datetime2?->format(\DateTimeInterface::ATOM);

        $articleSection = $gridMenu instanceof Menu
            ? $gridMenu->getAttribute('api_name')
            : $menu->getAttribute('api_name');

        return [
            'mode' => 'detail',
            'slug' => $dataSlug,
            'categories' => $this->mapCategories(
                gridMenus: $gridMenus,
                rootMenu: $menu,
                context: $context
            ),
            'item' => $this->mapGrid(
                grid: $grid,
                context: $context,
                gridMenu: $gridMenu instanceof Menu ? $gridMenu : null
            ),
            'seo' => $this->menuSeoService->buildMenuSeo(
                menu: $menu,
                locale: $context->locale,
                itemLinksByLocale: $this->resolveGridSlugMap($grid),
                overrides: $this->menuSeoService->resolveItemSeoDefaults(
                    menu: $menu,
                    locale: $context->locale,
                    title: $title,
                    description: $description,
                    metaTitle: $metaTitle,
                    metaDescription: $metaDescription,
                    metaKeywords: $metaKeywords,
                    image: $image,
                    articleSection: $articleSection,
                    publishedTime: $publishedTime,
                    modifiedTime: $modifiedTime
                )
            ),
        ];
    }

    /**
     * @param array<int, int> $menuIds
     */
    private function baseQuery(array $menuIds): Builder
    {
        return Grid::query()
            ->whereIn('menu_id', $menuIds)
            ->active()
            ->ordered();
    }

    private function relations(): array
    {
        $relations = [
            'media' => function ($query): void {
                $query->orderBy('sort_order')->orderBy('id');
            },
        ];

        if (! $this->hasGridRelatedProductsTable()) {
            return $relations;
        }

        $relations['relatedProductItems'] = function ($query): void {
            $query->orderBy('sort_order')
                ->orderBy('id')
                ->limit(self::RELATED_PRODUCTS_LIMIT);
        };

        $relations['relatedProductItems.variation.translations.language'] = function ($query): void {
        };

        $relations['relatedProductItems.variation.product'] = function ($query): void {
        };

        $relations['relatedProductItems.variation.mainMedia'] = function ($query): void {
        };

        $relations['relatedProductItems.variation.media'] = function ($query): void {
            $query->orderBy('sort_order')->orderBy('id');
        };

        $relations['relatedProductItems.variation.filterValues.translations.language'] = function ($query): void {
        };

        $relations['relatedProductItems.variation.filterValues.filter.translations.language'] = function ($query): void {
        };

        return $relations;
    }

    private function hasGridRelatedProductsTable(): bool
    {
        return Schema::hasTable('grids_related_products');
    }

    private function findGridByDataSlug(
        Builder $query,
        MenuDetailContext $context,
        string $dataSlug
    ): ?Grid {
        $locale = $context->locale;
        $fallbackLocale = $context->fallbackLocale;

        $grid = (clone $query)
            ->where("slug->{$locale}", $dataSlug)
            ->first();

        if ($grid !== null) {
            return $grid;
        }

        if ($fallbackLocale !== $locale) {
            $grid = (clone $query)
                ->where("slug->{$fallbackLocale}", $dataSlug)
                ->first();

            if ($grid !== null) {
                return $grid;
            }
        }

        if (ctype_digit($dataSlug)) {
            return (clone $query)
                ->whereKey((int) $dataSlug)
                ->first();
        }

        return null;
    }

    private function mapGrid(
        Grid $grid,
        MenuDetailContext $context,
        ?Menu $gridMenu = null
    ): array {
        return [
            'id' => (int) $grid->id,
            'menu_id' => (int) $grid->menu_id,
            'category' => $this->mapGridCategory($gridMenu, $context),
            'datetime1' => $grid->datetime1?->format('Y-m-d H:i:s'),
            'datetime2' => $grid->datetime2?->format('Y-m-d H:i:s'),
            'banner' => $grid->banner_url,
            'slug' => $this->localizedValue($grid->slug, $context),
            'multi_slugs' => $this->resolveGridSlugMap($grid),
            'name' => $this->localizedValue($grid->name, $context),
            'content' => $this->localizedValue($grid->content, $context),
            'location_or_group' => $this->localizedValue($grid->location_or_group, $context),
            'main_photo' => $grid->main_image_url,
            'files' => $this->mapMedia($grid),
            'related_products' => $this->mapRelatedProducts($grid, $context),
            'seo' => [
                'meta_title' => $this->localizedValue($grid->meta_title, $context),
                'meta_description' => $this->localizedValue($grid->meta_description, $context),
                'meta_keywords' => $this->localizedValue($grid->meta_keywords, $context),
            ],
        ];
    }

    private function mapGridCategory(
        ?Menu $gridMenu,
        MenuDetailContext $context
    ): ?array {
        if ($gridMenu === null) {
            return null;
        }

        $resource = (new MenuResource($gridMenu, false))
            ->resolve($context->request);

        $link = trim((string) (
            $resource['link']
            ?? $gridMenu->getAttribute('api_link')
            ?? ''
        ));

        $slug = trim((string) ($resource['slug'] ?? ''), '/');

        if ($slug === '') {
            $slug = trim($link, '/');
        }

        return [
            'id' => (int) $gridMenu->getKey(),
            'uuid' => $gridMenu->uuid !== null
                ? (string) $gridMenu->uuid
                : null,
            'parent_id' => $gridMenu->parent_id !== null
                ? (int) $gridMenu->parent_id
                : null,
            'name' => (string) (
                $gridMenu->getAttribute('api_name')
                ?? $resource['name']
                ?? ''
            ),
            'slug' => $slug,
            'logo' => $this->resolveCategoryLogo($resource, $gridMenu),
            'icon' => $this->resolveCategoryIcon($resource, $gridMenu),
        ];
    }

    private function mapCategories(
        EloquentCollection $gridMenus,
        Menu $rootMenu,
        MenuDetailContext $context
    ): array {
        return $gridMenus
            ->reject(
                fn (Menu $gridMenu): bool => (int) $gridMenu->getKey()
                    === (int) $rootMenu->getKey()
            )
            ->map(
                fn (Menu $gridMenu): array => $this->mapGridCategory(
                    gridMenu: $gridMenu,
                    context: $context
                )
            )
            ->values()
            ->all();
    }

    private function resolveCategoryLogo(
        array $resource,
        Menu $gridMenu
    ): ?string {
        return $this->resolveCategoryAsset([
            $resource['logo_url'] ?? null,
            data_get($resource, 'logo.url'),
            $resource['logo'] ?? null,
            $resource['main_image_url'] ?? null,
            data_get($resource, 'main_image.url'),
            $resource['main_image'] ?? null,
            $gridMenu->getAttribute('logo_url'),
            $gridMenu->getAttribute('logo'),
            $gridMenu->getAttribute('main_image_url'),
            $gridMenu->getAttribute('main_image'),
        ]);
    }

    private function resolveCategoryIcon(
        array $resource,
        Menu $gridMenu
    ): ?string {
        return $this->resolveCategoryAsset([
            $resource['icon_url'] ?? null,
            data_get($resource, 'icon.url'),
            $resource['icon'] ?? null,
            $resource['icon_image_url'] ?? null,
            data_get($resource, 'icon_image.url'),
            $resource['icon_image'] ?? null,
            $gridMenu->getAttribute('icon_url'),
            $gridMenu->getAttribute('icon'),
            $gridMenu->getAttribute('icon_image_url'),
            $gridMenu->getAttribute('icon_image'),
        ]);
    }

    private function resolveCategoryAsset(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            $assetUrl = $this->resolveCategoryAssetUrl($candidate);

            if ($assetUrl !== null) {
                return $assetUrl;
            }
        }

        return null;
    }

    private function resolveCategoryAssetUrl(mixed $candidate): ?string
    {
        if (! is_string($candidate)) {
            return null;
        }

        $candidate = trim($candidate);

        if ($candidate === '') {
            return null;
        }

        if (
            preg_match('/^https?:\/\//i', $candidate) === 1
            || str_starts_with($candidate, '//')
        ) {
            return $this->makeCategoryAssetUrlAbsolute($candidate);
        }

        $storagePath = ltrim($candidate, '/');

        if (str_starts_with($storagePath, 'storage/')) {
            $storagePath = substr($storagePath, strlen('storage/'));
        }

        if ($storagePath === '') {
            return null;
        }

        return $this->makeCategoryAssetUrlAbsolute(
            Storage::disk('public')->url($storagePath)
        );
    }

    private function makeCategoryAssetUrlAbsolute(string $assetUrl): string
    {
        $assetUrl = trim($assetUrl);

        if (preg_match('/^https?:\/\//i', $assetUrl) === 1) {
            return $assetUrl;
        }

        if (str_starts_with($assetUrl, '//')) {
            $scheme = parse_url(url('/'), PHP_URL_SCHEME);

            return (
                is_string($scheme) && $scheme !== ''
                    ? $scheme
                    : 'https'
                ) . ':' . $assetUrl;
        }

        return rtrim(url('/'), '/') . '/' . ltrim($assetUrl, '/');
    }

    private function hydrateGridMenus(
        EloquentCollection $gridMenus,
        MenuDetailContext $context
    ): void {
        foreach ($gridMenus as $gridMenu) {
            $this->menuApiHydrator->hydrate(
                menu: $gridMenu,
                locale: $context->locale,
                fallbackLocale: $context->fallbackLocale
            );
        }
    }

    /**
     * @return array<int, int>
     */
    private function resolveMenuIds(EloquentCollection $gridMenus): array
    {
        return $gridMenus
            ->map(
                fn (Menu $gridMenu): int => (int) $gridMenu->getKey()
            )
            ->values()
            ->all();
    }

    private function mapMedia(Grid $grid): array
    {
        if (! $grid->relationLoaded('media')) {
            return [];
        }

        return $grid->media
            ->map(fn ($media): array => [
                'id' => (int) $media->id,
                'type' => (string) $media->type,
                'path' => $media->path,
                'url' => $media->url,
                'original_name' => $media->original_name,
                'is_main' => (bool) $media->is_main,
                'sort_order' => (int) $media->sort_order,
            ])
            ->values()
            ->all();
    }

    private function mapRelatedProducts(
        Grid $grid,
        MenuDetailContext $context
    ): array {
        if (! $grid->relationLoaded('relatedProductItems')) {
            return [];
        }

        return $grid->relatedProductItems
            ->filter(fn ($item): bool => $item->variation !== null)
            ->take(self::RELATED_PRODUCTS_LIMIT)
            ->map(fn ($item): array => [
                'id' => (int) $item->id,
                'product_id' => (int) $item->product_id,
                'product_variation_id' => (int) $item->product_variation_id,
                'sort_order' => (int) $item->sort_order,
                'variation' => $this->mapVariation($item->variation, $context),
            ])
            ->values()
            ->all();
    }

    private function mapVariation(
        $variation,
        MenuDetailContext $context
    ): array {
        $translation = $this->resolveVariationTranslation(
            $variation->translations ?? collect(),
            $context
        );

        $mainMedia = $this->resolveVariationMainMedia($variation);

        return [
            'variation_id' => (int) $variation->id,
            'variation_uuid' => $variation->uuid,
            'product_id' => (int) $variation->product_id,
            'sku' => $variation->sku,
            'model' => $variation->model,
            'name' => $translation?->name ?? '',
            'slug' => $translation?->slug ?? '',
            'description' => $translation?->description ?? '',
            'stock' => (int) ($variation->stock ?? 0),
            'price' => $variation->price,
            'old_price' => $variation->old_price,
            'discount_price' => $variation->discount_price,
            'meta_title' => $translation?->meta_title,
            'meta_description' => $translation?->meta_description,
            'meta_keywords' => $translation?->meta_keywords,
            'main_image_path' => $mainMedia?->path,
            'main_image_url' => $mainMedia?->url,
            'gallery' => $this->mapVariationGallery($variation),
            'filters' => $this->mapVariationFilters($variation, $context),
        ];
    }

    private function resolveVariationTranslation(
        Collection $translations,
        MenuDetailContext $context
    ): mixed {
        $translation = $translations->first(function ($translation) use ($context): bool {
            return (string) ($translation->language?->code ?? '') === $context->locale;
        });

        if ($translation !== null) {
            return $translation;
        }

        $translation = $translations->first(function ($translation) use ($context): bool {
            return (string) ($translation->language?->code ?? '') === $context->fallbackLocale;
        });

        return $translation ?? $translations->first();
    }

    private function resolveVariationMainMedia($variation): mixed
    {
        if ($variation->relationLoaded('mainMedia') && $variation->mainMedia !== null) {
            return $variation->mainMedia;
        }

        if (! $variation->relationLoaded('media')) {
            return null;
        }

        return $variation->media->firstWhere('is_main', true)
            ?? $variation->media->first();
    }

    private function mapVariationGallery($variation): array
    {
        if (! $variation->relationLoaded('media')) {
            return [];
        }

        return $variation->media
            ->map(fn ($media): array => [
                'id' => (int) $media->id,
                'path' => $media->path,
                'url' => $media->url,
                'sort_order' => (int) ($media->sort_order ?? 0),
                'is_main' => (bool) ($media->is_main ?? false),
            ])
            ->values()
            ->all();
    }

    private function mapVariationFilters(
        $variation,
        MenuDetailContext $context
    ): array {
        if (! $variation->relationLoaded('filterValues')) {
            return [];
        }

        return $variation->filterValues
            ->map(function ($value) use ($context): array {
                $valueTranslation = $this->resolveFilterValueTranslation(
                    $value->translations ?? collect(),
                    $context
                );

                $filter = $value->filter;

                $filterTranslation = $filter !== null
                    ? $this->resolveFilterTranslation(
                        $filter->translations ?? collect(),
                        $context
                    )
                    : null;

                return [
                    'id' => (int) $value->id,
                    'name' => $valueTranslation?->name ?? '',
                    'slug' => $valueTranslation?->slug ?? '',
                    'filter_id' => (int) ($filter?->id ?? 0),
                    'filter_name' => $filterTranslation?->name ?? '',
                    'filter_slug' => $filterTranslation?->slug ?? '',
                ];
            })
            ->values()
            ->all();
    }

    private function resolveFilterValueTranslation(
        Collection $translations,
        MenuDetailContext $context
    ): mixed {
        return $this->resolveTranslationByLanguageCode(
            $translations,
            $context
        );
    }

    private function resolveFilterTranslation(
        Collection $translations,
        MenuDetailContext $context
    ): mixed {
        return $this->resolveTranslationByLanguageCode(
            $translations,
            $context
        );
    }

    private function resolveTranslationByLanguageCode(
        Collection $translations,
        MenuDetailContext $context
    ): mixed {
        $translation = $translations->first(function ($translation) use ($context): bool {
            return (string) ($translation->language?->code ?? '') === $context->locale;
        });

        if ($translation !== null) {
            return $translation;
        }

        $translation = $translations->first(function ($translation) use ($context): bool {
            return (string) ($translation->language?->code ?? '') === $context->fallbackLocale;
        });

        return $translation ?? $translations->first();
    }

    private function localizedValue(
        mixed $value,
        MenuDetailContext $context
    ): string {
        return LocalePicker::pickString(
            $value,
            $context->locale,
            $context->fallbackLocale
        ) ?? '';
    }

    private function resolveGridSlugMap(Grid $grid): array
    {
        $activeLanguageCodes = $this->menuSeoService->getActiveLanguageCodes();
        $slugMap = is_array($grid->slug) ? $grid->slug : [];

        $result = [];

        foreach ($activeLanguageCodes as $languageCode) {
            $value = $slugMap[$languageCode] ?? null;

            if (! is_string($value)) {
                continue;
            }

            $value = trim($value);

            if ($value === '') {
                continue;
            }

            $result[$languageCode] = trim($value, '/');
        }

        return $result;
    }
}
