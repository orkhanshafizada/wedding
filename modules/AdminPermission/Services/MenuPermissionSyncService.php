<?php

namespace Modules\AdminPermission\Services;

use App\Models\Language;
use Illuminate\Support\Facades\DB;
use Modules\AdminPermission\Models\AdminPermission;
use Modules\Menu\Models\Menu;
use Modules\Menu\Models\MenuTranslation;

class MenuPermissionSyncService
{
    public function sync(Menu $menu): void
    {
        DB::transaction(function () use ($menu): void {
            $menu = Menu::query()
                ->with('translations')
                ->find($menu->id);

            if (! $menu) {
                return;
            }

            foreach ($this->actions() as $action => $label) {
                AdminPermission::query()->updateOrCreate(
                    [
                        'name' => 'menu:' . $menu->id . '.' . $action,
                    ],
                    [
                        'display_name' => $this->displayName($menu, $label),
                        'group' => 'Menu Items',
                        'scope' => 'menu',
                        'module' => 'menu',
                        'action' => $action,
                        'menu_id' => $menu->id,
                        'sort_order' => $this->sortOrder($action),
                        'is_active' => true,
                    ]
                );
            }
        });
    }

    public function syncAll(): int
    {
        $count = 0;

        Menu::query()
            ->with('translations')
            ->orderBy('id')
            ->chunkById(100, function ($menus) use (&$count): void {
                foreach ($menus as $menu) {
                    $this->sync($menu);
                    $count++;
                }
            });

        return $count;
    }

    public function deleteForMenu(Menu $menu): void
    {
        AdminPermission::query()
            ->where('scope', 'menu')
            ->where('menu_id', $menu->id)
            ->delete();
    }

    private function actions(): array
    {
        return [
            'view' => 'View',
            'content' => 'Content',
            'edit' => 'Edit',
            'delete' => 'Delete',
        ];
    }

    private function displayName(Menu $menu, string $actionLabel): string
    {
        return $this->menuName($menu) . ' - ' . $actionLabel;
    }

    private function menuName(Menu $menu): string
    {
        if (! $menu->relationLoaded('translations')) {
            $menu->load('translations');
        }

        $adminLocale = $this->adminLocale();

        $name = $menu->translations
            ->firstWhere('locale', $adminLocale)?->name;

        if ($this->filledString($name)) {
            return trim((string) $name);
        }

        $defaultLocale = $this->defaultLocale();

        $name = $menu->translations
            ->firstWhere('locale', $defaultLocale)?->name;

        if ($this->filledString($name)) {
            return trim((string) $name);
        }

        $name = $menu->translations
            ->first(fn (MenuTranslation $translation): bool => $this->filledString($translation->name))
            ?->name;

        if ($this->filledString($name)) {
            return trim((string) $name);
        }

        return 'Menu #' . $menu->id;
    }

    private function adminLocale(): string
    {
        $locale = (string) (
        Language::query()
            ->where('is_default_admin', true)
            ->where('status', 'Active')
            ->value('code')
            ?: app()->getLocale()
        );

        return trim($locale) !== '' ? trim($locale) : (string) config('app.locale');
    }

    private function defaultLocale(): string
    {
        $locale = (string) (
        Language::query()
            ->where('is_default_site', true)
            ->where('status', 'Active')
            ->value('code')
            ?: config('app.locale')
        );

        return trim($locale) !== '' ? trim($locale) : (string) config('app.locale');
    }

    private function filledString(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }

    private function sortOrder(string $action): int
    {
        return match ($action) {
            'view' => 10,
            'content' => 20,
            'edit' => 30,
            'delete' => 40,
            default => 100,
        };
    }
}
