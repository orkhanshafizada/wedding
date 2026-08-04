<?php

namespace Modules\Menu\Services;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use LogicException;
use Modules\Menu\DTO\MenuDetailContext;
use Modules\Menu\Enums\MenuIncludedItemType;
use Modules\Menu\Http\Resources\MenuResource;
use Modules\Menu\Models\Menu;
use Modules\Menu\Models\MenuIncludedItem;
use Modules\Product\Http\Resources\Api\Product\ProductFilterResource;
use Modules\Product\Models\Filter\ProductFilter;
use Modules\Product\Models\Filter\ProductFilterTranslation;
use Modules\Product\Models\Filter\ProductFilterValue;
use Modules\Product\Models\Filter\ProductFilterValueTranslation;
use Modules\Slider\Http\Resources\SliderResource;

class MenuIncludedItemApiResolver
{
    private array $languages = [];

    public function __construct(
        private readonly MenuApiHydrator $menuApiHydrator,
        private readonly MenuApiDetailResolver $menuApiDetailResolver
    ) {
    }

    public function attachToMenu(Menu $menu, string $locale, string $fallbackLocale, ?Request $request = null): void
    {
        $menu->setAttribute(
            'api_included_items',
            $this->resolveForMenu($menu, $locale, $fallbackLocale, [(int) $menu->id], 0, $request)
        );
    }

    public function resolveForMenu(
        Menu $menu,
        string $locale,
        string $fallbackLocale,
        array $visitedIds = [],
        int $depth = 0,
        ?Request $request = null
    ): array {
        if ($depth >= 5) {
            return [];
        }

        $menu->loadMissing([
            'includedItems.includedMenu.translations',
            'includedItems.includedMenu.includedItems.includedMenu.translations',
            'includedItems.includedMenu.includedItems.slider.translations',
            'includedItems.includedMenu.includedItems.brandFilter.translations.language',
            'includedItems.includedMenu.includedItems.brandFilter.values.translations.language',
            'includedItems.slider.translations',
            'includedItems.brandFilter.translations.language',
            'includedItems.brandFilter.values.translations.language',
        ]);

        $currentRequest = $request ?? request();

        $context = new MenuDetailContext(
            request: $currentRequest,
            locale: $locale,
            fallbackLocale: $fallbackLocale
        );

        $result = [];

        foreach ($menu->includedItems as $includedItem) {
            $payload = match ($includedItem->included_type) {
                MenuIncludedItemType::MENU => $this->resolveMenuItem(
                    includedItem: $includedItem,
                    locale: $locale,
                    fallbackLocale: $fallbackLocale,
                    visitedIds: $visitedIds,
                    depth: $depth,
                    request: $currentRequest,
                    context: $context
                ),
                MenuIncludedItemType::SLIDER => $this->resolveSliderItem($includedItem, $currentRequest),
                MenuIncludedItemType::BRAND => $this->resolveBrandItem($includedItem, $locale, $fallbackLocale, $currentRequest),
                MenuIncludedItemType::SELF => $this->resolveSelfItem($menu, $includedItem, $currentRequest, $context),
            };

            if ($payload !== null) {
                $result[] = $payload;
            }
        }

        return $result;
    }

    private function resolveMenuItem(
        MenuIncludedItem $includedItem,
        string $locale,
        string $fallbackLocale,
        array $visitedIds,
        int $depth,
        Request $request,
        MenuDetailContext $context
    ): ?array {
        $includedMenu = $includedItem->includedMenu;

        if (! $includedMenu instanceof Menu) {
            return null;
        }

        $includedMenuId = (int) $includedMenu->id;

        if (in_array($includedMenuId, $visitedIds, true)) {
            return null;
        }

        $this->menuApiHydrator->hydrate($includedMenu, $locale, $fallbackLocale);

        try {
            $data = $this->menuApiDetailResolver->handle($includedMenu, $context);
        } catch (LogicException) {
            $data = null;
        }

        $nextVisitedIds = $visitedIds;
        $nextVisitedIds[] = $includedMenuId;

        $nestedIncludedItems = $this->resolveForMenu(
            menu: $includedMenu,
            locale: $locale,
            fallbackLocale: $fallbackLocale,
            visitedIds: $nextVisitedIds,
            depth: $depth + 1,
            request: $request
        );

        $includedMenu->setAttribute('api_included_items', $nestedIncludedItems);

        return [
            'included_type' => MenuIncludedItemType::MENU->value,
            'type' => (string) $includedMenu->getAttribute('api_type'),
            'sort_order' => (int) $includedItem->sort_order,
            'menu' => (new MenuResource($includedMenu))->resolve($request),
            'data' => $data,
            'included_items' => $nestedIncludedItems,
        ];
    }

    private function resolveSliderItem(MenuIncludedItem $includedItem, Request $request): ?array
    {
        $slider = $includedItem->slider;

        if (! $slider || ! $this->isActiveStatus($slider->status ?? null)) {
            return null;
        }

        return [
            'included_type' => MenuIncludedItemType::SLIDER->value,
            'type' => MenuIncludedItemType::SLIDER->value,
            'sort_order' => (int) $includedItem->sort_order,
            'menu' => null,
            'data' => (new SliderResource($slider))->resolve($request),
            'included_items' => [],
        ];
    }

    private function resolveBrandItem(MenuIncludedItem $includedItem, string $locale, string $fallbackLocale, Request $request): ?array
    {
        $filter = $includedItem->brandFilter;

        if (! $filter instanceof ProductFilter || ! $this->isActiveStatus($filter->status ?? null)) {
            return null;
        }

        return [
            'included_type' => MenuIncludedItemType::BRAND->value,
            'type' => MenuIncludedItemType::BRAND->value,
            'sort_order' => (int) $includedItem->sort_order,
            'menu' => null,
            'data' => (new ProductFilterResource($this->buildProductFilterPayload($filter, $locale, $fallbackLocale)))->resolve($request),
            'included_items' => [],
        ];
    }

    private function buildProductFilterPayload(ProductFilter $filter, string $locale, string $fallbackLocale): array
    {
        $language = $this->resolveLanguage($locale) ?: $this->resolveLanguage($fallbackLocale);

        $translation = $this->resolveFilterTranslation($filter, $language?->id);

        return [
            'filter_id' => (int) $filter->id,
            'name' => (string) ($translation?->name ?? ''),
            'slug' => (string) ($translation?->slug ?? ''),
            'input_type' => (string) ($filter->input_type ?? 'single'),
            'is_color_filter' => (bool) $filter->is_color_filter,
            'show_in_sidebar' => (bool) $filter->show_in_sidebar,
            'is_required' => (bool) $filter->is_required,
            'is_clickable' => (bool) $filter->is_clickable,
            'image' => $filter->image ? Storage::disk('public')->url((string) $filter->image) : null,
            'meta_title' => $translation?->meta_title,
            'meta_description' => $translation?->meta_description,
            'meta_keywords' => $translation?->meta_keywords,
            'values' => $filter->values
                ->filter(fn (ProductFilterValue $value): bool => $this->isActiveStatus($value->status ?? null) && (bool) $value->show_on_menu_detail)
                ->sortBy([
                    ['sort_order', 'asc'],
                    ['id', 'asc'],
                ])
                ->map(fn (ProductFilterValue $value): array => $this->buildProductFilterValuePayload($value, $language?->id))
                ->values()
                ->all(),
        ];
    }

    private function buildProductFilterValuePayload(ProductFilterValue $value, ?int $languageId): array
    {
        $translation = $this->resolveFilterValueTranslation($value, $languageId);

        return [
            'value_id' => (int) $value->id,
            'name' => (string) ($translation?->name ?? ''),
            'slug' => (string) ($translation?->slug ?? ''),
            'count' => 0,
            'color' => $value->color,
            'image' => $value->image ? Storage::disk('public')->url((string) $value->image) : null,
            'meta_title' => $translation?->meta_title,
            'meta_description' => $translation?->meta_description,
            'meta_keywords' => $translation?->meta_keywords,
        ];
    }

    private function resolveLanguage(string $locale): ?Language
    {
        $locale = trim($locale);

        if ($locale === '') {
            return null;
        }

        if (array_key_exists($locale, $this->languages)) {
            return $this->languages[$locale];
        }

        $this->languages[$locale] = Language::query()
            ->where('code', $locale)
            ->first();

        return $this->languages[$locale];
    }

    private function resolveFilterTranslation(ProductFilter $filter, ?int $languageId): ?ProductFilterTranslation
    {
        if ($languageId !== null) {
            $translation = $filter->translations->firstWhere('language_id', $languageId);

            if ($translation instanceof ProductFilterTranslation) {
                return $translation;
            }
        }

        $translation = $filter->translations->first();

        return $translation instanceof ProductFilterTranslation ? $translation : null;
    }

    private function resolveFilterValueTranslation(ProductFilterValue $value, ?int $languageId): ?ProductFilterValueTranslation
    {
        if ($languageId !== null) {
            $translation = $value->translations->firstWhere('language_id', $languageId);

            if ($translation instanceof ProductFilterValueTranslation) {
                return $translation;
            }
        }

        $translation = $value->translations->first();

        return $translation instanceof ProductFilterValueTranslation ? $translation : null;
    }

    private function isActiveStatus(mixed $status): bool
    {
        if (is_object($status) && property_exists($status, 'value')) {
            $status = $status->value;
        }

        return mb_strtolower(trim((string) $status)) === 'active';
    }

    private function resolveSelfItem(Menu $menu, MenuIncludedItem $includedItem, Request $request, MenuDetailContext $context): array
    {
        try {
            $data = $this->menuApiDetailResolver->handle($menu, $context);
        } catch (LogicException) {
            $data = null;
        }

        return [
            'included_type' => MenuIncludedItemType::SELF->value,
            'type' => (string) $menu->getAttribute('api_type'),
            'sort_order' => (int) $includedItem->sort_order,
            'menu' => (new MenuResource($menu))->resolve($request),
            'data' => $data,
            'included_items' => [],
        ];
    }
}
