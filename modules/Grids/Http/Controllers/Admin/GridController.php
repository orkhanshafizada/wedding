<?php

namespace Modules\Grids\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Upload\FileUploadService;
use App\Services\Upload\ImageUploadService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Grids\Http\Requests\StoreGridRequest;
use Modules\Grids\Http\Requests\UpdateGridRequest;
use Modules\Grids\Models\Grid;
use Modules\Grids\Models\GridMedia;
use Modules\Menu\Models\Menu;
use Modules\Product\Models\Variation\ProductVariation;

class GridController extends Controller
{
    public function __construct(
        private readonly ImageUploadService $imageUploadService,
        private readonly FileUploadService $fileUploadService
    ) {
    }

    public function index(Menu $menu): View
    {
        $grids = Grid::with(['media' => function ($query) {
            $query->orderBy('sort_order')->orderBy('id');
        }])
            ->where('menu_id', $menu->id)
            ->ordered()
            ->get();

        $columns = [
            ['label' => __('ID'), 'width' => '80'],
            ['label' => __('Main Image'), 'width' => '140'],
            ['label' => __('Banner'), 'width' => '140'],
            ['label' => __('Name')],
            ['label' => __('Date 1'), 'width' => '170'],
            ['label' => __('Date 2'), 'width' => '170'],
            ['label' => __('Location/Group')],
            ['label' => __('Status'), 'width' => '100'],
        ];

        $locale = app()->getLocale();

        $rows = $grids->map(function (Grid $grid) use ($locale) {
            $name = $grid->name[$locale] ?? $grid->name['az'] ?? '-';
            $location = $grid->location_or_group[$locale] ?? $grid->location_or_group['az'] ?? '-';

            $statusBadge = $grid->is_active
                ? '<span class="badge bg-success">' . e(__('Active')) . '</span>'
                : '<span class="badge bg-danger">' . e(__('Inactive')) . '</span>';

            $mainImageHtml = $grid->main_image_url
                ? '<img src="' . e($grid->main_image_url) . '" alt="" class="rounded" style="max-height: 50px; max-width: 100px; object-fit: cover;">'
                : '<span class="text-muted">-</span>';

            $bannerHtml = $grid->banner_url
                ? '<img src="' . e($grid->banner_url) . '" alt="" class="rounded" style="max-height: 50px; max-width: 100px; object-fit: cover;">'
                : '<span class="text-muted">-</span>';

            return [
                'id' => $grid->id,
                'cells' => [
                    $mainImageHtml,
                    $bannerHtml,
                    e($name),
                    $grid->datetime1 ? e($grid->datetime1->format('Y-m-d H:i')) : '-',
                    $grid->datetime2 ? e($grid->datetime2->format('Y-m-d H:i')) : '-',
                    e($location),
                    $statusBadge,
                ],
            ];
        });

        return view('grids::admin.index', compact('menu', 'columns', 'rows'));
    }

    public function create(Menu $menu): View
    {
        $productVariationOptions = $this->getProductVariationOptions();

        return view('grids::admin.create', compact('menu', 'productVariationOptions'));
    }

    public function store(StoreGridRequest $request, Menu $menu): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['menu_id'] = $menu->id;
            $data['sort_order'] = (int) Grid::where('menu_id', $menu->id)->max('sort_order') + 1;

            if ($request->hasFile('banner')) {
                $data['banner'] = $this->storeBanner($request->file('banner'), $menu->id);
            }

            $grid = Grid::create($data);

            if ($request->hasFile('media_files')) {
                $this->syncMediaUploads($grid, $request->file('media_files'), $request->input('media_new_main'));
            }

            $this->syncRelatedProducts($grid, (array) $request->input('related_product_variation_ids', []));
            $this->enforceSingleMain($grid);

            DB::commit();

            return redirect()
                ->route('admin.grids.index', $menu)
                ->with('success', __('Grid created successfully'));
        } catch (\Throwable $exception) {
            DB::rollBack();

            return back()
                ->with('error', __('An error occurred'))
                ->withInput();
        }
    }

    public function edit(Menu $menu, Grid $grid): View
    {
        $grid->load([
            'media' => function ($query) {
                $query->orderBy('sort_order')->orderBy('id');
            },
            'relatedProducts',
        ]);

        $productVariationOptions = $this->getProductVariationOptions();

        return view('grids::admin.edit', compact('menu', 'grid', 'productVariationOptions'));
    }

    public function update(UpdateGridRequest $request, Menu $menu, Grid $grid): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            if ((bool) $request->boolean('remove_banner')) {
                $this->deleteBannerFile($grid->banner);
                $data['banner'] = null;
            }

            if ($request->hasFile('banner')) {
                $this->deleteBannerFile($grid->banner);
                $data['banner'] = $this->storeBanner($request->file('banner'), $menu->id);
            }

            $grid->update($data);

            if ($request->has('media_existing')) {
                $this->syncExistingMedia($grid, $request->input('media_existing'));
            }

            if ($request->hasFile('media_files')) {
                $this->syncMediaUploads($grid, $request->file('media_files'), $request->input('media_new_main'));
            }

            $this->syncRelatedProducts($grid, (array) $request->input('related_product_variation_ids', []));
            $this->enforceSingleMain($grid);

            DB::commit();

            return redirect()
                ->route('admin.grids.index', $menu)
                ->with('success', __('Grid updated successfully'));
        } catch (\Throwable $exception) {
            DB::rollBack();

            return back()
                ->with('error', __('An error occurred'))
                ->withInput();
        }
    }

    public function destroy(Menu $menu, Grid $grid): RedirectResponse
    {
        try {
            foreach ($grid->media as $media) {
                $this->deleteMediaFile($media);
            }

            $this->deleteBannerFile($grid->banner);
            $grid->relatedProducts()->detach();
            $grid->delete();

            return redirect()
                ->route('admin.grids.index', $menu)
                ->with('success', __('Grid deleted successfully'));
        } catch (\Throwable $exception) {
            return back()->with('error', __('An error occurred'));
        }
    }

    public function bulkDelete(Request $request, Menu $menu): JsonResponse
    {
        try {
            $ids = array_filter((array) $request->input('ids', []));

            if ($ids === []) {
                return response()->json([
                    'success' => false,
                    'message' => __('No items selected'),
                ], 400);
            }

            $grids = Grid::with('media')
                ->where('menu_id', $menu->id)
                ->whereIn('id', $ids)
                ->get();

            foreach ($grids as $grid) {
                foreach ($grid->media as $media) {
                    $this->deleteMediaFile($media);
                }

                $this->deleteBannerFile($grid->banner);
                $grid->relatedProducts()->detach();
                $grid->delete();
            }

            return response()->json([
                'success' => true,
                'message' => __('Selected grids deleted successfully'),
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => __('An error occurred'),
            ], 500);
        }
    }

    public function updateOrder(Request $request, Menu $menu): JsonResponse
    {
        try {
            $order = (array) $request->input('order', []);

            foreach ($order as $index => $id) {
                Grid::where('id', $id)
                    ->where('menu_id', $menu->id)
                    ->update(['sort_order' => (int) $index]);
            }

            return response()->json([
                'success' => true,
                'message' => __('Order updated successfully'),
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => __('An error occurred'),
            ], 500);
        }
    }

    private function syncExistingMedia(Grid $grid, array $existingData): void
    {
        $models = GridMedia::where('grid_id', $grid->id)
            ->get()
            ->keyBy('id');

        foreach ($existingData as $mediaId => $row) {
            $mediaId = (int) $mediaId;

            if (!$models->has($mediaId)) {
                continue;
            }

            $media = $models->get($mediaId);

            $delete = (string) ($row['_delete'] ?? '0') === '1';

            if ($delete) {
                $this->deleteMediaFile($media);
                $media->delete();
                continue;
            }

            $isMain = false;
            if ($media->type === 'image') {
                $isMain = isset($row['is_main']) && (int) $row['is_main'] === 1;
            }

            $media->update([
                'sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : (int) $media->sort_order,
                'is_main' => $isMain,
            ]);
        }
    }

    private function syncMediaUploads(Grid $grid, array $files, mixed $mainIndex = null): void
    {
        $uploads = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $uploads[] = $file;
            }
        }

        if ($uploads === []) {
            return;
        }

        if ($mainIndex !== null && $mainIndex !== '') {
            GridMedia::where('grid_id', $grid->id)
                ->where('type', 'image')
                ->update(['is_main' => false]);
        }

        $maxSortOrder = GridMedia::where('grid_id', $grid->id)->max('sort_order');
        $nextSortOrder = $maxSortOrder !== null ? ((int) $maxSortOrder + 1) : 0;

        foreach ($uploads as $index => $file) {
            $directory = 'grids/' . $grid->menu_id . '/' . $grid->id . '/media';
            $mimeType = (string) $file->getMimeType();
            $isImage = str_starts_with($mimeType, 'image/');

            if ($isImage) {
                $path = $this->imageUploadService->uploadImage($file, $directory, 'grid-media');
                $type = 'image';
            } else {
                $path = $this->fileUploadService->storeRaw($file, $directory);
                $type = 'file';
            }

            $isMain = $isImage && $mainIndex !== null && $mainIndex !== '' && (int) $mainIndex === $index;

            GridMedia::create([
                'grid_id' => $grid->id,
                'type' => $type,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'sort_order' => $nextSortOrder + $index,
                'is_main' => $isMain,
            ]);
        }
    }

    private function syncRelatedProducts(Grid $grid, array $variationIds): void
    {
        $variationIds = array_values(array_unique(array_filter(array_map('intval', $variationIds))));

        if ($variationIds === []) {
            $grid->relatedProducts()->detach();
            return;
        }

        $variations = ProductVariation::query()
            ->whereIn('id', $variationIds)
            ->get(['id', 'product_id'])
            ->keyBy('id');

        $syncData = [];

        foreach ($variationIds as $index => $variationId) {
            $variation = $variations->get($variationId);

            if (!$variation) {
                continue;
            }

            $syncData[(int) $variation->product_id] = [
                'product_variation_id' => (int) $variation->id,
                'sort_order' => $index,
            ];
        }

        $grid->relatedProducts()->sync($syncData);
    }

    private function enforceSingleMain(Grid $grid): void
    {
        $items = GridMedia::query()
            ->where('grid_id', $grid->id)
            ->where('type', 'image')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($items->isEmpty()) {
            return;
        }

        $mainItems = $items->where('is_main', true)->values();

        if ($mainItems->isEmpty()) {
            $firstId = (int) $items->first()->id;

            GridMedia::query()
                ->where('grid_id', $grid->id)
                ->where('type', 'image')
                ->update(['is_main' => false]);

            GridMedia::query()
                ->whereKey($firstId)
                ->update(['is_main' => true]);

            return;
        }

        if ($mainItems->count() === 1) {
            return;
        }

        $keepId = (int) $mainItems->first()->id;

        GridMedia::query()
            ->where('grid_id', $grid->id)
            ->where('type', 'image')
            ->where('id', '!=', $keepId)
            ->update(['is_main' => false]);
    }

    private function getProductVariationOptions(): array
    {
        $locale = (string) app()->getLocale();
        $fallbackLocale = (string) config('app.fallback_locale', $locale);

        return ProductVariation::query()
            ->with(['translations.language'])
            ->orderBy('product_id')
            ->orderBy('id')
            ->get()
            ->map(function (ProductVariation $variation) use ($locale, $fallbackLocale) {
                return [
                    'id' => (int) $variation->id,
                    'product_id' => (int) $variation->product_id,
                    'label' => $this->buildVariationOptionLabel($variation, $locale, $fallbackLocale),
                ];
            })
            ->values()
            ->all();
    }

    private function buildVariationOptionLabel(ProductVariation $variation, string $locale, string $fallbackLocale): string
    {
        $name = $this->pickVariationTranslationValue($variation, 'name', $locale, $fallbackLocale);

        if ($name === '') {
            $name = trim((string) $variation->sku) !== '' ? (string) $variation->sku : (trim((string) $variation->model) !== '' ? (string) $variation->model : ('#' . $variation->id));
        }

        return 'P#' . (int) $variation->product_id . ' / V#' . (int) $variation->id . ' — ' . $name;
    }

    private function pickVariationTranslationValue(ProductVariation $variation, string $field, string $locale, string $fallbackLocale): string
    {
        $translations = $variation->translations ?? collect();

        $current = $translations->first(function ($translation) use ($locale) {
            return (string) ($translation->language?->code ?? '') === $locale;
        });

        $value = trim((string) data_get($current, $field, ''));
        if ($value !== '') {
            return $value;
        }

        if ($fallbackLocale !== $locale) {
            $fallback = $translations->first(function ($translation) use ($fallbackLocale) {
                return (string) ($translation->language?->code ?? '') === $fallbackLocale;
            });

            $value = trim((string) data_get($fallback, $field, ''));
            if ($value !== '') {
                return $value;
            }
        }

        foreach ($translations as $translation) {
            $value = trim((string) data_get($translation, $field, ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function storeBanner(UploadedFile $file, int $menuId): string
    {
        return $this->imageUploadService->uploadImage($file, 'grids/' . $menuId . '/banner', 'grid-banner');
    }

    private function deleteBannerFile(?string $path): void
    {
        $path = trim((string) $path);

        if ($path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function deleteMediaFile(GridMedia $media): void
    {
        $path = trim((string) $media->path);

        if ($path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
