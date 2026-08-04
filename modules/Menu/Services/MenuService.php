<?php

namespace Modules\Menu\Services;

use Illuminate\Support\Facades\DB;
use Modules\AdminPermission\Services\MenuPermissionSyncService;
use Modules\Menu\Enums\MenuIncludedItemType;
use Modules\Menu\Models\Menu;

class MenuService
{
    public function __construct(
        private readonly MenuPermissionSyncService $menuPermissionSyncService
    ) {
    }

    public function create(array $data, array $translations, array $includedItems = []): Menu
    {
        return DB::transaction(function () use ($data, $translations, $includedItems): Menu {
            $menu = Menu::query()->create($data);

            $menu->translations()->createMany($translations);

            $this->syncIncludedItems($menu, $includedItems);

            $menu = $menu->fresh([
                'translations',
                'includedItems.includedMenu.translations',
                'includedItems.brandFilter.translations',
                'includedItems.brandFilter.values.translations',
            ]);

            $this->menuPermissionSyncService->sync($menu);

            return $menu;
        });
    }

    public function update(Menu $menu, array $data, array $translations, array $includedItems = []): Menu
    {
        return DB::transaction(function () use ($menu, $data, $translations, $includedItems): Menu {
            $menu->update($data);

            foreach ($translations as $translation) {
                $menu->translations()
                    ->updateOrCreate(
                        [
                            'locale' => $translation['locale'],
                        ],
                        collect($translation)->except('locale')->toArray()
                    );
            }

            $this->syncIncludedItems($menu, $includedItems);

            $menu = $menu->fresh([
                'translations',
                'includedItems.includedMenu.translations',
                'includedItems.brandFilter.translations',
                'includedItems.brandFilter.values.translations',
            ]);

            $this->menuPermissionSyncService->sync($menu);

            return $menu;
        });
    }

    public function delete(Menu $menu): void
    {
        DB::transaction(function () use ($menu): void {
            $this->menuPermissionSyncService->deleteForMenu($menu);

            $menu->includedItems()->delete();
            $menu->delete();
        });
    }

    private function syncIncludedItems(Menu $menu, array $includedItems): void
    {
        $normalizedItems = collect($includedItems)
            ->map(function ($item): ?array {
                if (! is_array($item)) {
                    return null;
                }

                $type = trim((string) ($item['type'] ?? ''));
                $id = (int) ($item['id'] ?? 0);

                if (! in_array($type, MenuIncludedItemType::values(), true)) {
                    return null;
                }

                if (! $this->allowsZeroIncludedId($type) && $id <= 0) {
                    return null;
                }

                if ($this->allowsZeroIncludedId($type)) {
                    $id = 0;
                }

                return [
                    'type' => $type,
                    'id' => $id,
                ];
            })
            ->filter()
            ->reject(function (array $item) use ($menu): bool {
                return $item['type'] === MenuIncludedItemType::MENU->value && $item['id'] === (int) $menu->id;
            })
            ->unique(fn (array $item): string => $item['type'] . ':' . $item['id'])
            ->values();

        $menu->includedItems()->delete();

        foreach ($normalizedItems as $index => $item) {
            $menu->includedItems()->create([
                'included_type' => $item['type'],
                'included_id' => $item['id'],
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function allowsZeroIncludedId(string $type): bool
    {
        return in_array($type, [
            MenuIncludedItemType::SLIDER->value,
            MenuIncludedItemType::SELF->value,
        ], true);
    }
}
