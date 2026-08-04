<?php

namespace Modules\Gallery\Handlers\Api;

use Illuminate\Database\Eloquent\Builder;
use Modules\Gallery\Http\Resources\Api\GalleryAlbumItemResource;
use Modules\Gallery\Http\Resources\Api\GalleryAlbumResource;
use Modules\Gallery\Models\GalleryAlbum;
use Modules\Gallery\Models\GalleryAlbumItem;
use Modules\Menu\Contracts\MenuTypeApiHandler;
use Modules\Menu\DTO\MenuDetailContext;
use Modules\Menu\Enums\MenuType;
use Modules\Menu\Models\Menu;
use Modules\Menu\Services\MenuSeoService;
use Modules\Menu\Support\LocalePicker;

class GalleryMenuApiHandler implements MenuTypeApiHandler
{
    public function __construct(
        protected readonly MenuSeoService $menuSeoService
    ) {
    }

    public function handle(Menu $menu, MenuDetailContext $context): array
    {
        $request = $context->request;
        $request->attributes->set('api_locale', $context->locale);
        $request->attributes->set('api_fallback_locale', $context->fallbackLocale);

        $dataSlug = trim((string) $context->request->query('data_slug', ''));

        if ($dataSlug !== '') {
            return $this->detail($menu, $context, $dataSlug);
        }

        $showAlbumsExists = $this->albumQuery($menu)
            ->where('show_album', true)
            ->exists();

        if (!$showAlbumsExists) {
            return $this->flatItemsResponse($menu, $context);
        }

        return $this->mixedResponse($menu, $context);
    }

    private function mixedResponse(Menu $menu, MenuDetailContext $context): array
    {
        $perPage = $context->perPage(12);
        $page = $context->page(1);

        $baseQuery = $this->albumQuery($menu);
        $total = (int) (clone $baseQuery)->count();

        $albums = (clone $baseQuery)
            ->with($this->albumRelations($menu))
            ->forPage($page, $perPage)
            ->get();

        $visibleAlbums = $albums
            ->where('show_album', true)
            ->values();

        $hiddenAlbumItems = $albums
            ->where('show_album', false)
            ->flatMap(function (GalleryAlbum $album) {
                return $album->items;
            })
            ->sortBy([
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

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
            'menu_type' => $this->resolveMenuType($menu),
            'albums' => GalleryAlbumResource::collection($visibleAlbums)->resolve($context->request),
            'items' => GalleryAlbumItemResource::collection($hiddenAlbumItems)->resolve($context->request),
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
            'seo' => $seo,
        ];
    }

    private function flatItemsResponse(Menu $menu, MenuDetailContext $context): array
    {
        $perPage = $context->perPage(12);
        $page = $context->page(1);

        $baseQuery = $this->itemQuery($menu);
        $total = (int) (clone $baseQuery)->count();

        $items = (clone $baseQuery)
            ->with($this->itemRelations())
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
            'menu_type' => $this->resolveMenuType($menu),
            'items' => GalleryAlbumItemResource::collection($items)->resolve($context->request),
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
        $album = $this->findAlbumByDataSlug($menu, $context, $dataSlug);

        if ($album) {
            $title = $this->resolveAlbumTitle($album, $context);
            $description = $this->resolveAlbumDescription($album, $context);
            $image = $this->resolveAlbumImage($album);
            $publishedTime = $album->created_at?->format(\DateTimeInterface::ATOM);
            $modifiedTime = $album->updated_at?->format(\DateTimeInterface::ATOM);

            return [
                'mode' => 'detail',
                'detail_type' => 'album',
                'slug' => $dataSlug,
                'item' => (new GalleryAlbumResource($album))->resolve($context->request),
                'seo' => $this->menuSeoService->buildMenuSeo(
                    menu: $menu,
                    locale: $context->locale,
                    itemLinksByLocale: $this->resolveAlbumSlugMap($album),
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
                        modifiedTime: $modifiedTime,
                        ogType: 'article',
                        structuredType: 'ImageGallery'
                    )
                ),
            ];
        }

        $item = $this->findItemByDataSlug($menu, $context, $dataSlug);

        if (!$item) {
            abort(404, 'Gallery item not found.');
        }

        $title = $this->resolveItemTitle($item, $context);
        $description = $this->resolveItemDescription($item, $context);
        $image = $this->resolveItemImage($item);
        $publishedTime = $item->created_at?->format(\DateTimeInterface::ATOM);
        $modifiedTime = $item->updated_at?->format(\DateTimeInterface::ATOM);

        return [
            'mode' => 'detail',
            'detail_type' => 'item',
            'slug' => $dataSlug,
            'item' => (new GalleryAlbumItemResource($item))->resolve($context->request),
            'seo' => $this->menuSeoService->buildMenuSeo(
                menu: $menu,
                locale: $context->locale,
                itemLinksByLocale: $this->resolveItemSlugMap($item),
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
                    modifiedTime: $modifiedTime,
                    ogType: 'article',
                    structuredType: 'ImageObject'
                )
            ),
        ];
    }

    private function albumQuery(Menu $menu): Builder
    {
        return GalleryAlbum::query()
            ->where('menu_id', $menu->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    private function itemQuery(Menu $menu): Builder
    {
        return GalleryAlbumItem::query()
            ->where('is_active', true)
            ->whereHas('album', function (Builder $query) use ($menu) {
                $query->where('menu_id', $menu->id)
                    ->where('is_active', true);
            })
            ->when($this->usesPublicationFilter($menu), function (Builder $query) {
                $query->where('publication', true);
            })
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    private function albumRelations(Menu $menu): array
    {
        return [
            'translations',
            'items' => function ($query) use ($menu) {
                $query->where('is_active', true)
                    ->when($this->usesPublicationFilter($menu), function (Builder $builder) {
                        $builder->where('publication', true);
                    })
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->with([
                        'translations',
                        'album.translations',
                    ]);
            },
        ];
    }

    private function itemRelations(): array
    {
        return [
            'translations',
            'album.translations',
        ];
    }

    private function usesPublicationFilter(Menu $menu): bool
    {
        $menuType = $this->resolveMenuType($menu);

        return $menuType === MenuType::FILES->value;
    }

    private function resolveMenuType(Menu $menu): string
    {
        return $menu->type instanceof MenuType
            ? $menu->type->value
            : (string) $menu->type;
    }

    private function findAlbumByDataSlug(Menu $menu, MenuDetailContext $context, string $dataSlug): ?GalleryAlbum
    {
        $query = $this->albumQuery($menu)->with(['translations']);

        if (ctype_digit($dataSlug)) {
            return (clone $query)->whereKey((int) $dataSlug)->first();
        }

        $locale = $context->locale;
        $fallbackLocale = $context->fallbackLocale;

        $album = (clone $query)->whereHas('translations', function (Builder $builder) use ($locale, $dataSlug) {
            $builder->where('locale', $locale)->where('slug', $dataSlug);
        })->first();

        if ($album) {
            return $album;
        }

        if ($fallbackLocale !== $locale) {
            return (clone $query)->whereHas('translations', function (Builder $builder) use ($fallbackLocale, $dataSlug) {
                $builder->where('locale', $fallbackLocale)->where('slug', $dataSlug);
            })->first();
        }

        return null;
    }

    private function findItemByDataSlug(Menu $menu, MenuDetailContext $context, string $dataSlug): ?GalleryAlbumItem
    {
        $query = $this->itemQuery($menu)->with($this->itemRelations());

        if (ctype_digit($dataSlug)) {
            return (clone $query)->whereKey((int) $dataSlug)->first();
        }

        $locale = $context->locale;
        $fallbackLocale = $context->fallbackLocale;

        $item = (clone $query)->whereHas('translations', function (Builder $builder) use ($locale, $dataSlug) {
            $builder->where('locale', $locale)->where('slug', $dataSlug);
        })->first();

        if ($item) {
            return $item;
        }

        if ($fallbackLocale !== $locale) {
            return (clone $query)->whereHas('translations', function (Builder $builder) use ($fallbackLocale, $dataSlug) {
                $builder->where('locale', $fallbackLocale)->where('slug', $dataSlug);
            })->first();
        }

        return null;
    }

    private function resolveAlbumSlugMap(GalleryAlbum $album): array
    {
        $activeLanguageCodes = $this->menuSeoService->getActiveLanguageCodes();
        $translations = $album->relationLoaded('translations') ? $album->translations : collect();

        $result = [];

        foreach ($activeLanguageCodes as $languageCode) {
            $translation = $translations->firstWhere('locale', $languageCode);

            if (!$translation) {
                continue;
            }

            $slug = trim((string) ($translation->slug ?? ''));

            if ($slug === '') {
                continue;
            }

            $result[$languageCode] = trim($slug, '/');
        }

        return $result;
    }

    private function resolveItemSlugMap(GalleryAlbumItem $item): array
    {
        $activeLanguageCodes = $this->menuSeoService->getActiveLanguageCodes();
        $translations = $item->relationLoaded('translations') ? $item->translations : collect();

        $result = [];

        foreach ($activeLanguageCodes as $languageCode) {
            $translation = $translations->firstWhere('locale', $languageCode);

            if (!$translation) {
                continue;
            }

            $slug = trim((string) ($translation->slug ?? ''));

            if ($slug === '') {
                continue;
            }

            $result[$languageCode] = trim($slug, '/');
        }

        return $result;
    }

    private function resolveAlbumTitle(GalleryAlbum $album, MenuDetailContext $context): ?string
    {
        if (!$album->relationLoaded('translations')) {
            return null;
        }

        $translation = $album->translations->firstWhere('locale', $context->locale)
            ?? $album->translations->firstWhere('locale', $context->fallbackLocale)
            ?? $album->translations->first();

        return $translation ? trim((string) ($translation->title ?? $translation->name ?? '')) : null;
    }

    private function resolveAlbumDescription(GalleryAlbum $album, MenuDetailContext $context): ?string
    {
        if (!$album->relationLoaded('translations')) {
            return null;
        }

        $translation = $album->translations->firstWhere('locale', $context->locale)
            ?? $album->translations->firstWhere('locale', $context->fallbackLocale)
            ?? $album->translations->first();

        return $translation ? trim((string) ($translation->description ?? '')) : null;
    }

    private function resolveAlbumImage(GalleryAlbum $album): ?string
    {
        if (property_exists($album, 'image_url') || isset($album->image_url)) {
            return $album->image_url;
        }

        return null;
    }

    private function resolveItemTitle(GalleryAlbumItem $item, MenuDetailContext $context): ?string
    {
        if (!$item->relationLoaded('translations')) {
            return null;
        }

        $translation = $item->translations->firstWhere('locale', $context->locale)
            ?? $item->translations->firstWhere('locale', $context->fallbackLocale)
            ?? $item->translations->first();

        return $translation ? trim((string) ($translation->title ?? $translation->name ?? '')) : null;
    }

    private function resolveItemDescription(GalleryAlbumItem $item, MenuDetailContext $context): ?string
    {
        if (!$item->relationLoaded('translations')) {
            return null;
        }

        $translation = $item->translations->firstWhere('locale', $context->locale)
            ?? $item->translations->firstWhere('locale', $context->fallbackLocale)
            ?? $item->translations->first();

        return $translation ? trim((string) ($translation->description ?? '')) : null;
    }

    private function resolveItemImage(GalleryAlbumItem $item): ?string
    {
        if (property_exists($item, 'file_url') || isset($item->file_url)) {
            return $item->file_url;
        }

        if (property_exists($item, 'image_url') || isset($item->image_url)) {
            return $item->image_url;
        }

        return null;
    }
}
