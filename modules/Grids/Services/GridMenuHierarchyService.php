<?php

namespace Modules\Grids\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Menu\Enums\MenuType;
use Modules\Menu\Models\Menu;

class GridMenuHierarchyService
{
    /**
     * @return Collection<int, Menu>
     */
    public function resolve(Menu $rootMenu): Collection
    {
        $rootMenu->loadMissing('translations');

        $resolvedMenus = new Collection([$rootMenu]);
        $resolvedMenuIds = [
            (int) $rootMenu->getKey() => true,
        ];

        $parentMenuIds = [
            (int) $rootMenu->getKey(),
        ];

        while ($parentMenuIds !== []) {
            $childMenus = $this->getActiveGridChildren(
                parentMenuIds: $parentMenuIds,
                excludedMenuIds: array_keys($resolvedMenuIds)
            );

            if ($childMenus->isEmpty()) {
                break;
            }

            $nextParentMenuIds = [];

            foreach ($childMenus as $childMenu) {
                $childMenuId = (int) $childMenu->getKey();

                if (isset($resolvedMenuIds[$childMenuId])) {
                    continue;
                }

                $resolvedMenuIds[$childMenuId] = true;
                $nextParentMenuIds[] = $childMenuId;
                $resolvedMenus->push($childMenu);
            }

            $parentMenuIds = array_values(array_unique($nextParentMenuIds));
        }

        return $resolvedMenus;
    }

    /**
     * @param array<int, int> $parentMenuIds
     * @param array<int, int> $excludedMenuIds
     *
     * @return Collection<int, Menu>
     */
    private function getActiveGridChildren(
        array $parentMenuIds,
        array $excludedMenuIds
    ): Collection {
        $query = Menu::query()
            ->with('translations')
            ->whereIn('parent_id', $parentMenuIds)
            ->where('type', MenuType::GRIDS->value)
            ->where('status', true);

        if ($excludedMenuIds !== []) {
            $query->whereNotIn('id', $excludedMenuIds);
        }

        return $query
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
