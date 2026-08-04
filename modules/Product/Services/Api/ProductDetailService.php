<?php

namespace Modules\Product\Services\Api;

use App\Models\Language;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Modules\Menu\Models\Menu;
use Modules\Order\Services\OrderCheckoutService;
use Modules\Product\Models\Discount\DiscountHour;
use Modules\Product\Models\Filter\ProductFilterValue;
use Modules\Product\Models\Label\ProductLabel;
use Modules\Product\Models\Product;
use Modules\Product\Models\Variation\ProductVariation;
use Modules\Product\Models\Variation\ProductVariationMedia;
use Modules\Product\Models\Variation\ProductVariationTranslation;
use Modules\Comment\Services\CommentService;

class ProductDetailService
{
    public function __construct(
        private readonly OrderCheckoutService $orderCheckoutService,
        private readonly CommentService $commentService,
    ) {
    }

    public function handle(string $slug): array
    {
        $slug = trim($slug);

        if ($slug === '') {
            abort(422, __('Slug mütləqdir.'));
        }

        $languageId = $this->languageId();

        $resolved = $this->resolveByVariationSlug($slug, $languageId);

        /** @var Product $product */
        $product = $resolved['product'];
        /** @var ProductVariation $activeVariationModel */
        $activeVariationModel = $resolved['active_variation'];

        $menu = Menu::query()
            ->with(['translations', 'parent.translations', 'children.translations'])
            ->findOrFail((int) $product->main_category_id);

        $productDto = $this->productDtoFromVariation($product, $activeVariationModel, $languageId);

        $variations = $this->variationsOfProduct($product, $languageId);

        if ($variations === []) {
            abort(404, __('Məhsul tapılmadı.'));
        }

        $activeVariationFilters = $this->variationFilters($languageId, (int) $activeVariationModel->id);
        $activeVariation = $this->variationDto($activeVariationModel, $languageId, true, $activeVariationFilters);
        $variationsSorted = $this->variationsSortedWithActiveFirst($variations, (int) $activeVariationModel->id);

        $labels = $this->productLabels($product, $languageId);
        $related = $this->relatedProducts((int) $product->main_category_id, (int) $product->id, $languageId);
        $currentAmount = $this->currentVariationAmount($activeVariationModel);

        return [
            'menu' => $this->menuDto($menu),
            'breadcrumbs' => $this->breadcrumbsDto($menu),
            'subcategories' => $this->subcategoriesDto($menu),
            'product' => $productDto,
            'filters' => $activeVariationFilters,
            'active_variation' => $activeVariation,
            'variations' => $variationsSorted,
            'payment_methods' => $this->orderCheckoutService->paymentMethodsForAmount($currentAmount),
            'labels' => $labels,
            'comments' => $this->commentService->approvedForVariationPaginated((int) $activeVariationModel->id, 10),
            'related' => $related,
        ];
    }

    private function resolveByVariationSlug(string $slug, int $languageId): array
    {
        $translation = ProductVariationTranslation::query()
            ->where('language_id', $languageId)
            ->where('slug', $slug)
            ->first();

        if ($translation === null) {
            $translation = ProductVariationTranslation::query()
                ->where('slug', $slug)
                ->orderBy('language_id')
                ->first();
        }

        if ($translation === null) {
            abort(404, __('Məhsul tapılmadı.'));
        }

        $variation = ProductVariation::query()
            ->with(['product'])
            ->whereKey((int) $translation->product_variation_id)
            ->first();

        if ($variation === null || !$variation->product instanceof Product) {
            abort(404, __('Məhsul tapılmadı.'));
        }

        $product = $variation->product;

        $this->assertProductVisible($product);
        $this->assertVariationVisible($variation);

        return [
            'product' => $product,
            'active_variation' => $variation,
        ];
    }

    private function assertProductVisible(Product $product): void
    {
        if ($product->deleted_at !== null) {
            abort(404, __('Məhsul tapılmadı.'));
        }

        $status = $product->status;
        $isActive = $status === 'Active' || $status === 1 || $status === true || $status === '1';

        if (!$isActive) {
            abort(404, __('Məhsul tapılmadı.'));
        }
    }

    private function assertVariationVisible(ProductVariation $variation): void
    {
        if ($variation->deleted_at !== null) {
            abort(404, __('Məhsul tapılmadı.'));
        }
    }

    private function productDtoFromVariation(Product $product, ProductVariation $variation, int $languageId): array
    {
        $translation = $this->variationTranslation((int) $variation->id, $languageId);

        $tags = $product->tags;

        if (!is_array($tags)) {
            $decoded = json_decode((string) $tags, true);
            $tags = is_array($decoded) ? $decoded : [];
        }

        return [
            'id' => (int) $product->id,
            'name' => (string) ($translation?->name ?? ''),
            'description' => (string) ($translation?->description ?? ''),
            'slug' => (string) ($translation?->slug ?? ''),
            'sku' => $variation->sku,
            'model' => $variation->model,
            'tags' => $tags,
            'published_at' => $product->published_at,
            'meta_title' => $translation?->meta_title,
            'meta_description' => $translation?->meta_description,
            'meta_keywords' => $translation?->meta_keywords,
        ];
    }

    private function variationsOfProduct(Product $product, int $languageId): array
    {
        $models = ProductVariation::query()
            ->where('product_id', (int) $product->id)
            ->whereNull('deleted_at')
            ->orderByRaw('(stock > 0) DESC, id ASC')
            ->get();

        $output = [];

        foreach ($models as $variation) {
            $output[] = $this->variationDto($variation, $languageId, false);
        }

        return $output;
    }

    private function variationTranslation(int $variationId, int $languageId): ?ProductVariationTranslation
    {
        $translation = ProductVariationTranslation::query()
            ->where('product_variation_id', $variationId)
            ->where('language_id', $languageId)
            ->first();

        if ($translation !== null) {
            return $translation;
        }

        return ProductVariationTranslation::query()
            ->where('product_variation_id', $variationId)
            ->orderBy('language_id')
            ->first();
    }

    private function variationDto(ProductVariation $variation, int $languageId, bool $withFilters, ?array $prefetchedFilters = null): array
    {
        $translation = $this->variationTranslation((int) $variation->id, $languageId);

        $galleryRows = ProductVariationMedia::query()
            ->where('product_variation_id', (int) $variation->id)
            ->orderByDesc('is_main')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'path', 'sort_order', 'is_main']);

        $mainImage = null;
        $gallery = [];

        foreach ($galleryRows as $media) {
            $row = [
                'id' => (int) $media->id,
                'sort_order' => (int) $media->sort_order,
                'is_main' => (bool) $media->is_main,
            ];

            $path = (string) ($media->path ?? '');

            if ($path !== '') {
                $row['path'] = Storage::disk('public')->url($path);
            }

            $gallery[] = $row;

            if ($mainImage === null && (string) $media->path !== '') {
                $mainImage = (string) $media->path;
            }
        }

        $currentPrice = $this->currentVariationAmount($variation);

        $dto = [
            'id' => (int) $variation->id,
            'name' => (string) ($translation?->name ?? ''),
            'slug' => (string) ($translation?->slug ?? ''),
            'sku' => $variation->sku,
            'model' => $variation->model,
            'stock' => (int) $variation->stock,
            'price' => (float) $variation->price,
            'old_price' => $variation->old_price !== null ? (float) $variation->old_price : null,
            'discount_price' => $variation->discount_price !== null ? (float) $variation->discount_price : null,
            'current_price' => $currentPrice,
            'meta_title' => $translation?->meta_title,
            'meta_description' => $translation?->meta_description,
            'meta_keywords' => $translation?->meta_keywords,
            'main_image_path' => $mainImage,
            'gallery' => $gallery,
            'filters' => [],
        ];

        if ($withFilters) {
            $dto['filters'] = $prefetchedFilters ?? $this->variationFilters($languageId, (int) $variation->id);
        }

        return $dto;
    }

    private function variationsSortedWithActiveFirst(array $variations, int $activeVariationId): array
    {
        $active = null;
        $rest = [];

        foreach ($variations as $variation) {
            if ((int) ($variation['id'] ?? 0) === $activeVariationId) {
                $active = $variation;
                continue;
            }

            $rest[] = $variation;
        }

        if ($active !== null) {
            array_unshift($rest, $active);
        }

        return array_values($rest);
    }

    private function variationFilters(int $languageId, int $variationId): array
    {
        $rows = ProductFilterValue::query()
            ->join('product_variation_filter_value as pvfv', 'product_filter_values.id', '=', 'pvfv.product_filter_value_id')
            ->join('product_filters as pf', 'pf.id', '=', 'product_filter_values.product_filter_id')
            ->leftJoin('product_filter_translations as pft', function ($join) use ($languageId): void {
                $join->on('pft.product_filter_id', '=', 'pf.id')
                    ->where('pft.language_id', '=', $languageId);
            })
            ->leftJoin('product_filter_value_translations as pfvt', function ($join) use ($languageId): void {
                $join->on('pfvt.product_filter_value_id', '=', 'product_filter_values.id')
                    ->where('pfvt.language_id', '=', $languageId);
            })
            ->where('pvfv.product_variation_id', $variationId)
            ->whereNull('pf.deleted_at')
            ->whereNull('product_filter_values.deleted_at')
            ->where(function ($query): void {
                $query->where('pf.status', 'Active')
                    ->orWhere('pf.status', 1)
                    ->orWhere('pf.status', true)
                    ->orWhere('pf.status', '1');
            })
            ->where(function ($query): void {
                $query->where('product_filter_values.status', 'Active')
                    ->orWhere('product_filter_values.status', 1)
                    ->orWhere('product_filter_values.status', true)
                    ->orWhere('product_filter_values.status', '1');
            })
            ->orderBy('pf.sort_order')
            ->orderBy('pf.id')
            ->orderBy('product_filter_values.sort_order')
            ->orderBy('product_filter_values.id')
            ->get([
                'pf.id as filter_id',
                'pf.input_type',
                'pf.is_color_filter',
                'pf.show_in_sidebar',
                'pf.is_required',
                'pf.is_clickable',
                'pf.image',
                'pft.name as filter_name',
                'pft.slug as filter_slug',
                'pft.meta_title as filter_meta_title',
                'pft.meta_description as filter_meta_description',
                'pft.meta_keywords as filter_meta_keywords',
                'product_filter_values.id as value_id',
                'product_filter_values.color',
                'product_filter_values.image as value_image',
                'pfvt.name as value_name',
                'pfvt.slug as value_slug',
                'pfvt.meta_title as value_meta_title',
                'pfvt.meta_description as value_meta_description',
                'pfvt.meta_keywords as value_meta_keywords',
            ]);

        $grouped = [];

        foreach ($rows as $row) {
            $filterId = (int) $row->filter_id;

            if (!isset($grouped[$filterId])) {
                $grouped[$filterId] = [
                    'filter_id' => $filterId,
                    'name' => (string) ($row->filter_name ?? ''),
                    'slug' => (string) ($row->filter_slug ?? ''),
                    'input_type' => (string) $row->input_type,
                    'is_color_filter' => (bool) $row->is_color_filter,
                    'show_in_sidebar' => (bool) $row->show_in_sidebar,
                    'is_required' => (bool) $row->is_required,
                    'is_clickable' => (bool) $row->is_clickable,
                    'image' => $row->image,
                    'meta_title' => $row->filter_meta_title,
                    'meta_description' => $row->filter_meta_description,
                    'meta_keywords' => $row->filter_meta_keywords,
                    'values' => [],
                ];
            }

            $grouped[$filterId]['values'][] = [
                'value_id' => (int) $row->value_id,
                'name' => (string) ($row->value_name ?? ''),
                'slug' => (string) ($row->value_slug ?? ''),
                'count' => 0,
                'color' => $row->color,
                'image' => $row->value_image,
                'meta_title' => $row->value_meta_title,
                'meta_description' => $row->value_meta_description,
                'meta_keywords' => $row->value_meta_keywords,
            ];
        }

        return array_values($grouped);
    }

    private function productLabels(Product $product, int $languageId): array
    {
        $labels = ProductLabel::query()
            ->select(['product_labels.*'])
            ->join('product_label_product as product_label_pivot', 'product_label_pivot.product_label_id', '=', 'product_labels.id')
            ->where('product_label_pivot.product_id', (int) $product->id)
            ->whereNull('product_labels.deleted_at')
            ->where(function ($query): void {
                $query->where('product_labels.status', 'Active')
                    ->orWhere('product_labels.status', 1)
                    ->orWhere('product_labels.status', true)
                    ->orWhere('product_labels.status', '1');
            })
            ->orderBy('product_labels.sort_order')
            ->orderBy('product_labels.id')
            ->get();

        $output = [];

        foreach ($labels as $label) {
            $translation = $label->translations()->where('language_id', $languageId)->first();

            if ($translation === null) {
                $translation = $label->translations()->orderBy('language_id')->first();
            }

            $output[] = [
                'id' => (int) $label->id,
                'name' => (string) ($translation?->name ?? ''),
                'slug' => (string) ($translation?->slug ?? ''),
                'color' => $label->color,
                'background' => $label->background,
                'meta_title' => $translation?->meta_title,
                'meta_description' => $translation?->meta_description,
                'meta_keywords' => $translation?->meta_keywords,
            ];
        }

        return $output;
    }

    private function relatedProducts(int $mainCategoryId, int $excludeProductId, int $languageId): array
    {
        $menuIds = $this->descendantMenuIdsCached($mainCategoryId);

        $products = Product::query()
            ->whereIn('main_category_id', $menuIds)
            ->where('id', '<>', $excludeProductId)
            ->whereNull('deleted_at')
            ->where(function ($query): void {
                $query->where('status', 'Active')
                    ->orWhere('status', 1)
                    ->orWhere('status', true)
                    ->orWhere('status', '1');
            })
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        $output = [];

        foreach ($products as $product) {
            $variation = $this->defaultVariationOfProduct((int) $product->id);

            if ($variation === null) {
                continue;
            }

            $translation = $this->variationTranslation((int) $variation->id, $languageId);

            $mainImage = ProductVariationMedia::query()
                ->where('product_variation_id', (int) $variation->id)
                ->orderByDesc('is_main')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->value('path');

            $output[] = [
                'product_id' => (int) $product->id,
                'variation_id' => (int) $variation->id,
                'name' => (string) ($translation?->name ?? ''),
                'slug' => (string) ($translation?->slug ?? ''),
                'sku' => $variation->sku,
                'model' => $variation->model,
                'meta_title' => $translation?->meta_title,
                'meta_description' => $translation?->meta_description,
                'meta_keywords' => $translation?->meta_keywords,
                'price' => (float) $variation->price,
                'old_price' => $variation->old_price !== null ? (float) $variation->old_price : null,
                'discount_price' => $variation->discount_price !== null ? (float) $variation->discount_price : null,
                'current_price' => $this->currentVariationAmount($variation),
                'stock' => (int) $variation->stock,
                'main_image' => $mainImage,
            ];
        }

        return $output;
    }

    private function defaultVariationOfProduct(int $productId): ?ProductVariation
    {
        return ProductVariation::query()
            ->where('product_id', $productId)
            ->whereNull('deleted_at')
            ->orderByRaw('(stock > 0) DESC, id ASC')
            ->first();
    }

    private function languageId(): int
    {
        $locale = (string) app()->getLocale();

        return Cache::remember("lang_id:{$locale}", 600, static function () use ($locale): int {
            $language = Language::query()->where('code', $locale)->first();

            if ($language !== null) {
                return (int) $language->id;
            }

            $fallbackLocale = (string) config('app.fallback_locale', 'az');
            $fallbackLanguage = Language::query()->where('code', $fallbackLocale)->first();

            return (int) ($fallbackLanguage?->id ?? 1);
        });
    }

    private function descendantMenuIdsCached(int $rootMenuId): array
    {
        return Cache::remember("menu_descendants:{$rootMenuId}", 600, function () use ($rootMenuId): array {
            $ids = [$rootMenuId];
            $queue = [$rootMenuId];

            while ($queue !== []) {
                $batch = $queue;
                $queue = [];

                $children = Menu::query()
                    ->whereIn('parent_id', $batch)
                    ->pluck('id')
                    ->map(static fn ($value) => (int) $value)
                    ->all();

                foreach ($children as $childId) {
                    if (!in_array($childId, $ids, true)) {
                        $ids[] = $childId;
                        $queue[] = $childId;
                    }
                }
            }

            return $ids;
        });
    }

    private function menuDto(Menu $menu): array
    {
        $translation = $menu->translations->firstWhere('locale', app()->getLocale()) ?? $menu->translations->first();

        return [
            'id' => (int) $menu->id,
            'name' => (string) ($translation?->name ?? ''),
            'link' => (string) ($translation?->link ?? ''),
            'meta_title' => $translation?->meta_title,
            'meta_description' => $translation?->meta_description,
            'meta_keywords' => $translation?->meta_keywords,
        ];
    }

    private function breadcrumbsDto(Menu $menu): array
    {
        $breadcrumbs = [];
        $node = $menu;

        while ($node !== null) {
            $translation = $node->translations->firstWhere('locale', app()->getLocale()) ?? $node->translations->first();

            $breadcrumbs[] = [
                'id' => (int) $node->id,
                'name' => (string) ($translation?->name ?? ''),
                'link' => (string) ($translation?->link ?? ''),
                'meta_title' => $translation?->meta_title,
                'meta_description' => $translation?->meta_description,
                'meta_keywords' => $translation?->meta_keywords,
            ];

            $node = $node->parent;
        }

        return array_reverse($breadcrumbs);
    }

    private function subcategoriesDto(Menu $menu): array
    {
        if ($menu->children->isEmpty()) {
            return [];
        }

        $output = [];

        foreach ($menu->children as $child) {
            $translation = $child->translations->firstWhere('locale', app()->getLocale()) ?? $child->translations->first();

            $output[] = [
                'id' => (int) $child->id,
                'name' => (string) ($translation?->name ?? ''),
                'link' => (string) ($translation?->link ?? ''),
                'meta_title' => $translation?->meta_title,
                'meta_description' => $translation?->meta_description,
                'meta_keywords' => $translation?->meta_keywords,
            ];
        }

        return $output;
    }

    private function currentVariationAmount(ProductVariation $variation): float
    {
        $discountPrice = (float) ($variation->discount_price ?? 0);

        if ($discountPrice > 0 && $this->variationHasActiveDiscountHour($variation)) {
            return $this->money($discountPrice);
        }

        return $this->money((float) ($variation->price ?? 0));
    }

    private function variationHasActiveDiscountHour(ProductVariation $variation): bool
    {
        $variationId = (int) $variation->id;
        $productId = (int) $variation->product_id;

        return DiscountHour::query()
            ->where('status', 'Active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->whereHas('items', function ($query) use ($variationId, $productId) {
                $query->where(function ($builder) use ($variationId, $productId) {
                    $builder->where('product_variation_id', $variationId);

                    if ($productId > 0) {
                        $builder->orWhere('product_id', $productId);
                    }
                });
            })
            ->exists();
    }

    private function money(float $value): float
    {
        return (float) number_format($value, 2, '.', '');
    }
}
