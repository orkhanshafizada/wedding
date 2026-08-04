<?php

namespace Modules\Menu\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use Closure;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Modules\Menu\Http\Requests\Api\Menu\IndexMenuRequest;
use Modules\Menu\Http\Resources\MenuResource;
use Modules\Menu\Models\Menu;
use Modules\Menu\Models\MenuContent;
use Modules\Menu\Services\MenuApiHydrator;
use Modules\Menu\Services\MenuIncludedItemApiResolver;
use Modules\Menu\Support\LocalePicker;

class MenuController extends BaseApiController
{
    public function __construct(
        private readonly MenuIncludedItemApiResolver $menuIncludedItemApiResolver,
        private readonly MenuApiHydrator $menuApiHydrator
    ) {
    }

    public function index(IndexMenuRequest $request): JsonResponse
    {
        $locale = trim((string) $request->query('locale', ''));

        if ($locale === '') {
            $locale = app()->getLocale();
        }

        $fallbackLocale = (string) config('app.locale');

        $onlyHeader = $request->has('in_header') && $request->boolean('in_header');
        $onlyFooter = $request->has('in_footer') && $request->boolean('in_footer');
        $onlyMainPage = $request->has('show_on_main_page') && $request->boolean('show_on_main_page');

        $menus = Menu::query()
            ->with([
                'translations',
                'childrenRecursive',
            ])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $this->hydrateCollectionApiFields($menus, $locale, $fallbackLocale);

        $allMenusFlat = $this->flattenMenus($menus);

        $this->loadIncludedItems($allMenusFlat);
        $this->attachIncludedItems($menus, $locale, $fallbackLocale, $request);

        $baseMenuMatcher = $this->baseMenuMatcher($request);

        $mainPageMenus = $this->buildScopedMenuTree($menus, 'show_on_main_page', [], $baseMenuMatcher);
        $header = $this->buildScopedMenuTree($menus, 'in_header', [], $baseMenuMatcher);
        $footer = $this->buildScopedMenuTree($menus, 'in_footer', [], $baseMenuMatcher);

        if ($onlyHeader || $onlyFooter || $onlyMainPage) {
            $payload = [];

            if ($onlyMainPage) {
                $payload['main_page_menus'] = MenuResource::collection($mainPageMenus);
            }

            if ($onlyHeader) {
                $payload['header'] = MenuResource::collection($header);
            }

            if ($onlyFooter) {
                $payload['footer'] = MenuResource::collection($footer);
            }

            return $this->response($payload, 'OK');
        }

        return $this->response([
            'header' => MenuResource::collection($header),
            'footer' => MenuResource::collection($footer),
            'main_page_menus' => MenuResource::collection($mainPageMenus),
        ], 'OK');
    }

    private function hydrateCollectionApiFields(Collection $menus, string $locale, string $fallbackLocale): void
    {
        foreach ($menus as $menu) {
            $this->menuApiHydrator->hydrate($menu, $locale, $fallbackLocale);

            if ($menu->relationLoaded('childrenRecursive') && $menu->childrenRecursive->isNotEmpty()) {
                $this->hydrateCollectionApiFields($menu->childrenRecursive, $locale, $fallbackLocale);
            }
        }
    }

    private function loadIncludedItems(Collection $menus): void
    {
        if ($menus->isEmpty()) {
            return;
        }

        (new EloquentCollection($menus->all()))->loadMissing([
            'includedItems.includedMenu.translations',
            'includedItems.includedMenu.includedItems.includedMenu.translations',
            'includedItems.includedMenu.includedItems.slider.translations',
            'includedItems.includedMenu.includedItems.brandFilter.translations.language',
            'includedItems.includedMenu.includedItems.brandFilter.values.translations.language',
            'includedItems.slider.translations',
            'includedItems.brandFilter.translations.language',
            'includedItems.brandFilter.values.translations.language',
        ]);
    }

    private function attachIncludedItems(Collection $menus, string $locale, string $fallbackLocale, IndexMenuRequest $request): void
    {
        foreach ($menus as $menu) {
            $this->menuIncludedItemApiResolver->attachToMenu($menu, $locale, $fallbackLocale, $request);

            if ($menu->relationLoaded('childrenRecursive') && $menu->childrenRecursive->isNotEmpty()) {
                $this->attachIncludedItems($menu->childrenRecursive, $locale, $fallbackLocale, $request);
            }
        }
    }

    private function flattenMenus(Collection $menus): Collection
    {
        $result = collect();

        foreach ($menus as $menu) {
            $result->push($menu);

            if ($menu->relationLoaded('childrenRecursive') && $menu->childrenRecursive->isNotEmpty()) {
                $result = $result->merge($this->flattenMenus($menu->childrenRecursive));
            }
        }

        return $result->values();
    }

    private function buildScopedMenuTree(Collection $menus, string $visibilityColumn, array $excludedIds, Closure $baseMenuMatcher): Collection
    {
        $result = collect();

        foreach ($menus as $menu) {
            $result = $result->merge(
                $this->buildScopedMenuNodes($menu, $visibilityColumn, $excludedIds, $baseMenuMatcher)
            );
        }

        return $result->values();
    }

    private function buildScopedMenuNodes(Menu $menu, string $visibilityColumn, array $excludedIds, Closure $baseMenuMatcher): Collection
    {
        $children = collect();

        if ($menu->relationLoaded('childrenRecursive') && $menu->childrenRecursive->isNotEmpty()) {
            foreach ($menu->childrenRecursive as $child) {
                $children = $children->merge(
                    $this->buildScopedMenuNodes($child, $visibilityColumn, $excludedIds, $baseMenuMatcher)
                );
            }
        }

        $menuCanBeReturned = $baseMenuMatcher($menu)
            && (bool) $menu->{$visibilityColumn}
            && ! in_array((int) $menu->id, $excludedIds, true);

        if (! $menuCanBeReturned) {
            return $children->values();
        }

        $clonedMenu = clone $menu;
        $clonedMenu->setRelation('childrenRecursive', $children->values());
        $clonedMenu->setRelation('children', $children->values());

        return collect([$clonedMenu]);
    }

    private function baseMenuMatcher(IndexMenuRequest $request): Closure
    {
        $status = $request->has('status') ? $request->boolean('status') : true;
        $type = $request->filled('type') ? trim((string) $request->query('type')) : null;
        $viewType = $request->filled('view_type') ? mb_strtolower(trim((string) $request->query('view_type'))) : null;
        $parentId = $request->filled('parent_id') ? (int) $request->query('parent_id') : null;
        $link = $request->filled('link') ? $this->normalizeLink((string) $request->query('link')) : null;
        $search = $request->filled('q') ? mb_strtolower(trim((string) $request->query('q'))) : null;

        return function (Menu $menu) use ($status, $type, $viewType, $parentId, $link, $search): bool {
            if ((bool) $menu->status !== $status) {
                return false;
            }

            if ($type !== null && $this->normalizeTypeValue($menu->type) !== $type) {
                return false;
            }

            if ($viewType !== null) {
                $menuViewType = $menu->view_type !== null ? trim((string) $menu->view_type) : '';
                $menuViewType = mb_strtolower($menuViewType === '' ? 'default' : $menuViewType);

                if ($menuViewType !== $viewType) {
                    return false;
                }
            }

            if ($parentId !== null && (int) $menu->parent_id !== $parentId) {
                return false;
            }

            if ($link !== null && $this->normalizeLink((string) $menu->getAttribute('api_link')) !== $link) {
                return false;
            }

            if ($search !== null) {
                $name = mb_strtolower((string) $menu->getAttribute('api_name'));

                if (! str_contains($name, $search)) {
                    return false;
                }
            }

            return true;
        };
    }

    private function normalizeLink(string $link): string
    {
        $link = trim($link);

        if ($link === '') {
            return '/';
        }

        return str_starts_with($link, '/') ? $link : '/' . $link;
    }

    private function normalizeTypeValue(mixed $type): string
    {
        if (is_object($type) && property_exists($type, 'value')) {
            return trim((string) $type->value);
        }

        return trim((string) $type);
    }

    private function buildContentMenuDetails(Collection $menus, string $locale, string $fallbackLocale): array
    {
        if ($menus->isEmpty()) {
            return [];
        }

        $menuIds = $menus
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $pages = MenuContent::query()
            ->with(['files' => static function ($query): void {
                $query->orderBy('sort_order')->orderBy('id');
            }])
            ->whereIn('menu_id', $menuIds)
            ->get()
            ->keyBy('menu_id');

        $publicDisk = Storage::disk('public');
        $result = [];

        foreach ($menus as $menu) {
            $page = $pages->get((int) $menu->id);

            $data = $page?->data ?? [];
            $contentData = LocalePicker::pickArray($data, $locale, $fallbackLocale, [
                'title' => '',
                'description' => '',
            ]);

            $mainPhotoUrl = null;

            if (! empty($page?->main_photo)) {
                $mainPhotoUrl = $publicDisk->url($page->main_photo);
            }

            $files = [];

            if ($page && $page->relationLoaded('files')) {
                foreach ($page->files as $file) {
                    $files[] = [
                        'id' => $file->id,
                        'path' => $file->path,
                        'url' => $publicDisk->url($file->path),
                        'original_name' => $file->original_name,
                        'extension' => $file->extension,
                        'mime_type' => $file->mime_type,
                        'size' => $file->size,
                        'sort_order' => $file->sort_order,
                    ];
                }
            }

            $result[] = [
                'type' => 'content',
                'menu' => (new MenuResource($menu))->resolve(),
                'data' => [
                    'title' => (string) ($contentData['title'] ?? ''),
                    'description' => (string) ($contentData['description'] ?? ''),
                    'main_photo' => $mainPhotoUrl,
                    'files' => $files,
                    'seo' => [
                        'meta_title' => $menu->getAttribute('api_meta_title'),
                        'meta_description' => $menu->getAttribute('api_meta_description'),
                        'meta_keywords' => $menu->getAttribute('api_meta_keywords'),
                    ],
                ],
            ];
        }

        return $result;
    }
}
