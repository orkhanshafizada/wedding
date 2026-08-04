<?php

namespace Modules\MainPage\Services;

use App\Models\Language;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Banner\Models\Banner;
use Modules\MainPage\Enums\MainPageSectionSourceType;
use Modules\MainPage\Models\MainPageSection;
use Modules\Menu\Enums\ContentType;
use Modules\Menu\Enums\MenuType;
use Modules\Menu\Models\Menu;
use Modules\Product\Models\Block\ProductBlock;

class MainPageSectionService
{
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return MainPageSection::query()
            ->with([
                'translations.language',
            ])
            ->ordered()
            ->paginate($perPage);
    }

    public function create(array $data, array $titles): MainPageSection
    {
        return DB::transaction(function () use ($data, $titles): MainPageSection {
            $section = MainPageSection::query()->create($this->normalizeData($data));
            $this->syncTranslations($section, $titles);

            return $section->load(['translations.language']);
        });
    }

    public function update(MainPageSection $section, array $data, array $titles): MainPageSection
    {
        return DB::transaction(function () use ($section, $data, $titles): MainPageSection {
            $section->update($this->normalizeData($data));
            $this->syncTranslations($section, $titles);

            return $section->load(['translations.language']);
        });
    }

    public function delete(MainPageSection $section): void
    {
        DB::transaction(function () use ($section): void {
            $section->delete();
        });
    }

    public function updateOrder(array $order): void
    {
        DB::transaction(function () use ($order): void {
            foreach ($order as $item) {
                MainPageSection::query()
                    ->whereKey((int) $item['id'])
                    ->update([
                        'sort_order' => (int) $item['sort_order'],
                    ]);
            }
        });
    }

    public function sourceTypeOptions(): array
    {
        return MainPageSectionSourceType::options();
    }

    public function menuTypeOptions(): array
    {
        $types = collect(MenuType::cases())
            ->sortBy(function (MenuType $type): array {
                return [
                    $type === MenuType::CATEGORIES ? 1 : 0,
                    $type->label(),
                ];
            })
            ->values();

        return $types->map(static function (MenuType $type): array {
            return [
                'value' => $type->value,
                'label' => $type->label(),
            ];
        })->all();
    }

    public function sourceReferences(string $sourceType, ?string $menuType = null): array
    {
        return match ($sourceType) {
            MainPageSectionSourceType::BANNER->value => $this->bannerOptions(),
            MainPageSectionSourceType::PRODUCT_BLOCK->value => $this->productBlockOptions(),
            MainPageSectionSourceType::BRAND->value => [],
            MainPageSectionSourceType::SHOW_ON_MAIN_PAGE_CATEGORIES->value => [],
            MainPageSectionSourceType::SHOW_ON_MAIN_PAGE_SERVICES->value => [],
            MainPageSectionSourceType::MENU_TYPE->value => $this->menuTypeReferenceOptions($menuType),
            default => [],
        };
    }

    private function bannerOptions(): array
    {
        return Banner::query()
            ->with('translations')
            ->active()
            ->ordered()
            ->get()
            ->groupBy('position')
            ->map(function ($items, $position): array {
                $first = $items->first();

                return [
                    'value' => (string) $position,
                    'label' => (string) ($first?->position_name ?? $position),
                ];
            })
            ->values()
            ->all();
    }

    private function productBlockOptions(): array
    {
        $languageId = Language::query()
            ->where('code', app()->getLocale())
            ->value('id');

        return ProductBlock::query()
            ->with('translations')
            ->active()
            ->ordered()
            ->get()
            ->map(function (ProductBlock $block) use ($languageId): array {
                $translation = $block->translations->firstWhere('language_id', (int) $languageId)
                    ?? $block->translations->first();

                return [
                    'value' => (string) $block->id,
                    'label' => (string) ($translation?->title ?? ('#' . $block->id)),
                ];
            })
            ->all();
    }

    private function menuTypeReferenceOptions(?string $menuType = null): array
    {
        if (!$menuType) {
            return [];
        }

        $roots = Menu::query()
            ->with(['translations', 'childrenRecursive.translations'])
            ->active()
            ->where('type', $menuType)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($roots->isNotEmpty()) {
            return $this->buildTreeOptions($roots);
        }

        $menus = Menu::query()
            ->with('translations')
            ->active()
            ->where('type', $menuType)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $menus->map(function (Menu $menu): array {
            $translation = $menu->translations->firstWhere('locale', app()->getLocale())
                ?? $menu->translations->first();

            return [
                'value' => (string) $menu->id,
                'label' => (string) ($translation?->name ?? ('#' . $menu->id)),
            ];
        })->all();
    }

    private function buildTreeOptions(Collection $roots): array
    {
        $items = [];

        foreach ($roots as $root) {
            $this->appendTreeOption($items, $root, 0);
        }

        return $items;
    }

    private function appendTreeOption(array &$items, Menu $menu, int $depth): void
    {
        $translation = $menu->translations->firstWhere('locale', app()->getLocale())
            ?? $menu->translations->first();

        $prefix = $depth > 0 ? str_repeat('-', $depth) . ' ' : '';

        $items[] = [
            'value' => (string) $menu->id,
            'label' => $prefix . (string) ($translation?->name ?? ('#' . $menu->id)),
        ];

        $children = collect($menu->childrenRecursive ?? [])
            ->sortBy([
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        foreach ($children as $child) {
            if (!(bool) $child->status) {
                continue;
            }

            $this->appendTreeOption($items, $child, $depth + 1);
        }
    }

    private function syncTranslations(MainPageSection $section, array $titles): void
    {
        $languageIds = Language::query()->pluck('id')->map(static fn ($id): int => (int) $id)->all();

        foreach ($languageIds as $languageId) {
            $title = isset($titles[$languageId]) ? trim((string) $titles[$languageId]) : null;

            $section->translations()->updateOrCreate(
                ['language_id' => $languageId],
                ['title' => $title !== '' ? $title : null]
            );
        }
    }

    private function normalizeData(array $data): array
    {
        $sourceType = (string) $data['source_type'];

        $menuType = $sourceType === MainPageSectionSourceType::MENU_TYPE->value
            ? (($data['menu_type'] ?? null) ?: null)
            : null;

        $menuViewType = $sourceType === MainPageSectionSourceType::MENU_TYPE->value
            ? (($data['menu_view_type'] ?? null) ?: null)
            : null;

        $sourceReference = in_array($sourceType, [
            MainPageSectionSourceType::BANNER->value,
            MainPageSectionSourceType::PRODUCT_BLOCK->value,
            MainPageSectionSourceType::MENU_TYPE->value,
        ], true)
            ? (($data['source_reference'] ?? null) ?: null)
            : null;

        return [
            'source_type' => $sourceType,
            'source_reference' => $sourceReference,
            'menu_type' => $menuType,
            'menu_view_type' => $menuViewType,
            'limit' => !empty($data['limit']) ? (int) $data['limit'] : null,
            'sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
            'status' => (string) $data['status'],
        ];
    }
}
