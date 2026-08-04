<?php

namespace Modules\Menu\Http\Controllers\Admin;

use App\Enums\StatusEnum;
use App\Models\Language;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\AdminPermission\Services\AdminAccessService;
use Modules\Form\Enums\FormType;
use Modules\Grids\Enums\GridType;
use Modules\Menu\Enums\ContentType;
use Modules\Menu\Enums\MenuIncludedItemType;
use Modules\Menu\Enums\MenuType;
use Modules\Menu\Http\Requests\StoreMenuRequest;
use Modules\Menu\Http\Requests\UpdateMenuRequest;
use Modules\Menu\Models\Menu;
use Modules\Menu\Services\MenuService;
use Modules\Product\Models\Filter\ProductFilter;

class MenuController extends Controller
{
    public function __construct(
        private readonly MenuService $service,
        private readonly AdminAccessService $accessService
    ) {
        $this->middleware('permission:menu.view')->only('index');
        $this->middleware('permission:menu.create')->only(['create', 'store']);
        $this->middleware('permission:menu.edit,menu')->only(['edit', 'update', 'reorder', 'toggle']);
        $this->middleware('permission:menu.delete,menu')->only('destroy');
    }

    public function index(): View
    {
        $user = $this->adminUser();

        $tree = $this->visibleRootMenus($user);

        $types = $this->allowedMenuTypes($user);

        return view('menu::admin.menu.index', [
            'tree' => $tree,
            'types' => $types,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $this->adminUser();

        $types = $this->allowedMenuTypes($user);

        abort_if($types === [], 403);

        $parentTree = $this->selectableParentTree($user);

        $selectedParentId = $request->integer('parent_id') ?: null;

        if ($selectedParentId !== null && ! $this->menuIdExistsInTree($parentTree, $selectedParentId)) {
            $selectedParentId = null;
        }

        $languages = Language::where('status', StatusEnum::ACTIVE)
            ->orderBy('sort_order')
            ->get();

        $viewTypeOptionsByType = $this->viewTypeOptionsByType($types);
        $requiredLanguageCodes = $this->requiredLanguageCodes();
        $includedItemOptions = $this->buildIncludedItemOptions($parentTree);

        return view('menu::admin.menu.create', compact(
            'types',
            'parentTree',
            'selectedParentId',
            'languages',
            'viewTypeOptionsByType',
            'requiredLanguageCodes',
            'includedItemOptions'
        ));
    }

    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('icon_image')) {
            $data['icon_image'] = $request->file('icon_image')->store('menus/icons', 'public');
            $data['icon'] = null;
        } else {
            $data['icon_image'] = null;
            $data['icon'] = $data['icon'] ?? null;
        }

        if ($request->hasFile('main_image')) {
            $data['main_image'] = $request->file('main_image')->store('menus/main-images', 'public');
        } else {
            $data['main_image'] = null;
        }

        $translations = $data['translations'];
        $includedItems = $data['included_items'] ?? [];

        unset($data['translations'], $data['included_items']);

        $this->service->create($data, $translations, $includedItems);

        return redirect()
            ->route('admin.menus.index')
            ->with('success', __('Menu created'));
    }

    public function edit(Menu $menu): View
    {
        $user = $this->adminUser();

        abort_unless($this->accessService->can($user, 'menu.edit', $menu), 403);

        $menu->load([
            'translations',
            'includedItems.includedMenu.translations',
            'includedItems.brandFilter.translations',
            'includedItems.brandFilter.values.translations',
        ]);

        $types = $this->allowedMenuTypes($user, $menu);

        abort_if($types === [], 403);

        $parentTree = $this->selectableParentTree($user, (int) $menu->id);

        $languages = Language::where('status', StatusEnum::ACTIVE)
            ->orderBy('sort_order')
            ->get();

        $viewTypeOptionsByType = $this->viewTypeOptionsByType($types);
        $requiredLanguageCodes = $this->requiredLanguageCodes();
        $includedItemOptions = $this->buildIncludedItemOptions($parentTree, (int) $menu->id);

        return view('menu::admin.menu.edit', compact(
            'menu',
            'types',
            'parentTree',
            'languages',
            'viewTypeOptionsByType',
            'requiredLanguageCodes',
            'includedItemOptions'
        ));
    }

    public function update(UpdateMenuRequest $request, Menu $menu): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('icon_image')) {
            if ($menu->icon_image) {
                Storage::disk('public')->delete($menu->icon_image);
            }

            $data['icon_image'] = $request->file('icon_image')->store('menus/icons', 'public');
            $data['icon'] = null;
        } else {
            $keep = (bool) $request->boolean('keep_icon_image', true);

            if (! $keep && $menu->icon_image) {
                Storage::disk('public')->delete($menu->icon_image);
                $data['icon_image'] = null;
            } else {
                unset($data['icon_image']);
            }

            $data['icon'] = $data['icon'] ?? $menu->icon;
        }

        if ($request->hasFile('main_image')) {
            if ($menu->main_image) {
                Storage::disk('public')->delete($menu->main_image);
            }

            $data['main_image'] = $request->file('main_image')->store('menus/main-images', 'public');
        } else {
            $keepMainImage = (bool) $request->boolean('keep_main_image', true);

            if (! $keepMainImage && $menu->main_image) {
                Storage::disk('public')->delete($menu->main_image);
                $data['main_image'] = null;
            } else {
                unset($data['main_image']);
            }
        }

        $translations = $data['translations'];
        $includedItems = $data['included_items'] ?? [];

        unset($data['translations'], $data['included_items'], $data['keep_main_image']);

        $this->service->update($menu, $data, $translations, $includedItems);

        return redirect()
            ->route('admin.menus.index')
            ->with('success', __('Menu updated'));
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $user = $this->adminUser();

        abort_unless($this->accessService->can($user, 'menu.delete', $menu), 403);

        if ($menu->icon_image) {
            Storage::disk('public')->delete($menu->icon_image);
        }

        if ($menu->main_image) {
            Storage::disk('public')->delete($menu->main_image);
        }

        $this->service->delete($menu);

        return redirect()
            ->route('admin.menus.index')
            ->with('success', __('Menu deleted'));
    }

    public function reorder(Request $request): JsonResponse
    {
        $user = $this->adminUser();

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:menus,id'],
            'items.*.parent_id' => ['nullable', 'integer', 'exists:menus,id'],
            'items.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($data['items'] as $row) {
            $menu = Menu::query()->find((int) $row['id']);

            abort_unless($menu && $this->accessService->can($user, 'menu.edit', $menu), 403);
        }

        DB::transaction(function () use ($data): void {
            foreach ($data['items'] as $row) {
                Menu::where('id', $row['id'])->update([
                    'parent_id' => $row['parent_id'] ?? null,
                    'sort_order' => $row['sort_order'],
                ]);
            }
        });

        return response()->json(['ok' => true]);
    }

    public function toggle(Request $request, Menu $menu): JsonResponse
    {
        $user = $this->adminUser();

        abort_unless($this->accessService->can($user, 'menu.edit', $menu), 403);

        $payload = $request->validate([
            'field' => ['required', 'in:status,in_header,in_footer,show_on_main_page'],
            'value' => ['required', 'boolean'],
        ]);

        $menu->update([
            $payload['field'] => (bool) $payload['value'],
        ]);

        return response()->json(['ok' => true]);
    }

    private function viewTypeOptionsByType(array $types = []): array
    {
        $allowedTypeValues = collect($types)
            ->map(fn ($type): string => $type instanceof MenuType ? $type->value : (string) $type)
            ->filter()
            ->values()
            ->all();

        $map = [];

        if (enum_exists(GridType::class) && in_array(MenuType::GRIDS->value, $allowedTypeValues, true)) {
            $map[MenuType::GRIDS->value] = array_map(
                static fn ($case) => [
                    'value' => $case->value,
                    'label' => ucfirst($case->value),
                ],
                GridType::cases()
            );
        }

        if (enum_exists(FormType::class) && in_array(MenuType::FORM->value, $allowedTypeValues, true)) {
            $map[MenuType::FORM->value] = array_map(
                static fn ($case) => [
                    'value' => $case->value,
                    'label' => ucfirst($case->value),
                ],
                FormType::cases()
            );
        }

        if (enum_exists(ContentType::class) && in_array(MenuType::CONTENT->value, $allowedTypeValues, true)) {
            $map[MenuType::CONTENT->value] = array_map(
                static fn ($case) => [
                    'value' => $case->value,
                    'label' => ucfirst($case->value),
                ],
                ContentType::cases()
            );
        }

        return $map;
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

    private function selectableParentTree(User $user, ?int $excludeId = null): Collection
    {
        if ($this->accessService->isSuperAdmin($user) || $this->accessService->can($user, 'menu.edit')) {
            $query = Menu::with(['translations', 'childrenRecursive.translations', 'parent.translations'])
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->orderBy('id');

            if ($excludeId !== null) {
                $query->where('id', '!=', $excludeId);
            }

            return $query->get();
        }

        $allowedMenuIds = $this->accessService->allowedMenuIdsForActions($user, ['view', 'content', 'edit', 'delete']);

        if ($allowedMenuIds === []) {
            return collect();
        }

        if ($excludeId !== null) {
            $allowedMenuIds = array_values(array_filter(
                $allowedMenuIds,
                fn (int $menuId): bool => $menuId !== $excludeId
            ));
        }

        if ($allowedMenuIds === []) {
            return collect();
        }

        $tree = Menu::with(['translations', 'childrenRecursive.translations', 'parent.translations'])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $this->filterTreeByIds($tree, $allowedMenuIds);
    }

    private function filterTreeByIds(Collection $tree, array $allowedMenuIds): Collection
    {
        $allowedMenuIds = collect($allowedMenuIds)
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $tree
            ->map(function (Menu $menu) use ($allowedMenuIds): ?Menu {
                $children = $menu->relationLoaded('childrenRecursive')
                    ? $this->filterTreeByIds($menu->childrenRecursive, $allowedMenuIds)
                    : collect();

                $isAllowed = in_array((int) $menu->id, $allowedMenuIds, true);

                if (! $isAllowed && $children->isEmpty()) {
                    return null;
                }

                $menu->setRelation('childrenRecursive', $children);
                $menu->setRelation('children', $children);

                return $menu;
            })
            ->filter()
            ->values();
    }

    private function menuIdExistsInTree(Collection $tree, int $menuId): bool
    {
        foreach ($tree as $menu) {
            if ((int) $menu->id === $menuId) {
                return true;
            }

            $children = $menu->relationLoaded('childrenRecursive')
                ? $menu->childrenRecursive
                : collect();

            if ($children->isNotEmpty() && $this->menuIdExistsInTree($children, $menuId)) {
                return true;
            }
        }

        return false;
    }

    private function requiredLanguageCodes(): array
    {
        $codes = Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->where('is_required', 1)
            ->orderBy('sort_order')
            ->pluck('code')
            ->map(static fn ($value): string => (string) $value)
            ->filter(static fn ($value): bool => $value !== '')
            ->values()
            ->all();

        if ($codes !== []) {
            return $codes;
        }

        $fallbackCode = (string) (Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->orderByDesc('is_default_admin')
            ->orderBy('sort_order')
            ->value('code') ?? '');

        return $fallbackCode !== '' ? [$fallbackCode] : [];
    }

    private function menuDisplayName(Menu $menu, string $locale): string
    {
        return trim((string) (
            $menu->translations->firstWhere('locale', $locale)?->name
            ?? $menu->translations->first()?->name
            ?? ('#' . $menu->id)
        ));
    }

    private function menuPath(Menu $menu, string $locale): array
    {
        $segments = [];
        $currentMenu = $menu;

        while ($currentMenu) {
            array_unshift($segments, $this->menuDisplayName($currentMenu, $locale));

            if (! $currentMenu->relationLoaded('parent')) {
                $currentMenu->load('parent.translations');
            }

            $currentMenu = $currentMenu->parent;
        }

        return $segments;
    }

    private function formatIncludedMenuLabel(Menu $menu, int $level, string $locale): string
    {
        $path = $this->menuPath($menu, $locale);
        $currentName = end($path) ?: ('#' . $menu->id);
        $ancestorPath = count($path) > 1 ? implode('->', array_slice($path, 0, -1)) : null;
        $prefix = $level > 0 ? str_repeat('-', $level) . ' ' : '';

        if ($ancestorPath) {
            return $prefix . $currentName . ' (' . $ancestorPath . ')';
        }

        return $prefix . $currentName;
    }

    private function buildIncludedItemOptions(Collection $tree, ?int $excludeId = null): array
    {
        return array_merge(
            $this->buildIncludedSelfOptions(),
            $this->buildIncludedMenuOptions($tree, $excludeId),
            $this->buildIncludedSliderOptions(),
            $this->buildIncludedBrandFilterOptions()
        );
    }

    private function buildIncludedSelfOptions(): array
    {
        return [
            [
                'type' => MenuIncludedItemType::SELF->value,
                'id' => 0,
                'label' => '[' . __('Current menu data') . '] ' . __('Current menu data'),
                'search' => mb_strtolower(trim('current menu data own menu data self')),
            ],
        ];
    }
    private function buildIncludedMenuOptions(Collection $tree, ?int $excludeId = null): array
    {
        $locale = (string) app()->getLocale();
        $options = [];

        $walker = function (Collection $nodes, int $level = 0) use (&$walker, &$options, $locale, $excludeId): void {
            foreach ($nodes as $node) {
                if ($excludeId !== null && (int) $node->id === $excludeId) {
                    if ($node->relationLoaded('childrenRecursive') && $node->childrenRecursive->isNotEmpty()) {
                        $walker($node->childrenRecursive, $level + 1);
                    }

                    continue;
                }

                if (! $node->relationLoaded('parent')) {
                    $node->load('parent.translations');
                }

                $label = $this->formatIncludedMenuLabel($node, $level, $locale);
                $path = implode(' ', $this->menuPath($node, $locale));

                $options[] = [
                    'type' => MenuIncludedItemType::MENU->value,
                    'id' => (int) $node->id,
                    'label' => '[' . __('Menu') . '] ' . $label,
                    'search' => mb_strtolower(trim('menu ' . $label . ' ' . $path)),
                ];

                if ($node->relationLoaded('childrenRecursive') && $node->childrenRecursive->isNotEmpty()) {
                    foreach ($node->childrenRecursive as $childNode) {
                        if (! $childNode->relationLoaded('parent')) {
                            $childNode->setRelation('parent', $node);
                        }
                    }

                    $walker($node->childrenRecursive, $level + 1);
                }
            }
        };

        $walker($tree);

        return $options;
    }

    private function buildIncludedSliderOptions(): array
    {
        return [
            [
                'type' => MenuIncludedItemType::SLIDER->value,
                'id' => 0,
                'label' => '[' . __('Slider') . '] ' . __('All sliders'),
                'search' => mb_strtolower(trim('slider sliders all sliders')),
            ],
        ];
    }

    private function buildIncludedBrandFilterOptions(): array
    {
        $keywords = [
            'brand',
            'brands',
            'brend',
            'brendi',
            'brendler',
            'brendlər',
            'бренд',
            'бренды',
            'бренди',
        ];

        return ProductFilter::query()
            ->with(['translations.language', 'values.translations.language'])
            ->whereNull('deleted_at')
            ->where(function ($query) use ($keywords): void {
                $query->whereHas('translations', function ($translationQuery) use ($keywords): void {
                    foreach ($keywords as $keyword) {
                        $translationQuery->orWhere('name', 'like', '%' . $keyword . '%')
                            ->orWhere('slug', 'like', '%' . $keyword . '%');
                    }
                });
            })
            ->ordered()
            ->get()
            ->map(function (ProductFilter $filter): array {
                $translation = $filter->getTranslation((string) app()->getLocale());
                $label = trim((string) ($translation?->name ?: ('Brand Filter #' . $filter->id)));

                return [
                    'type' => MenuIncludedItemType::BRAND->value,
                    'id' => (int) $filter->id,
                    'label' => '[' . __('Brand') . '] ' . $label,
                    'search' => mb_strtolower(trim('brand brend brands ' . $label . ' ' . ($translation?->slug ?? ''))),
                ];
            })
            ->values()
            ->all();
    }

    private function adminUser(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }

    private function menuTypeValue(Menu $menu): string
    {
        return $menu->type instanceof \BackedEnum
            ? (string) $menu->type->value
            : (string) $menu->type;
    }
}
