<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Modules\AdminPermission\Services\AdminAccessService;
use Modules\Menu\Enums\MenuType;
use Modules\Menu\Models\Menu;
use Modules\Menu\Services\MenuService;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AdminAccessService $accessService
    ) {
    }


    public function index(): RedirectResponse
    {
        return redirect()->route('admin.form.view-data', [
            'menu' => '1ebac386-32db-481f-8c0b-1fcfa8650d92',
        ]);
    }

    private function allowedMenuTypes(User $user, ?Menu $currentMenu = null): array
    {
        if ($this->accessService->isSuperAdmin($user)) {
            return MenuType::cases();
        }

        $allowedTypeValues = $this->accessService->allowedMenuTypeValues($user);

        if ($currentMenu !== null) {
            $currentType = $this->menuTypeValue($currentMenu);

            if ($currentType !== '') {
                $allowedTypeValues[] = $currentType;
            }
        }

        $allowedTypeValues = collect($allowedTypeValues)
            ->map(fn ($value): string => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return collect(MenuType::cases())
            ->filter(fn (MenuType $type): bool => in_array($type->value, $allowedTypeValues, true))
            ->values()
            ->all();
    }

    private function visibleRootMenus(User $user): Collection
    {
        if ($this->accessService->isSuperAdmin($user) || $this->accessService->can($user, 'menu.view')) {
            return Menu::with([
                'translations',
                'childrenRecursive.translations',
            ])
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        }

        $allowedMenuIds = $this->accessService->allowedMenuIdsForActions($user, ['view', 'content', 'edit', 'delete']);

        if ($allowedMenuIds === []) {
            return collect();
        }

        $tree = Menu::with([
            'translations',
            'childrenRecursive.translations',
        ])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $this->filterTreeByIds($tree, $allowedMenuIds);
    }

    private function adminUser(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }

}
