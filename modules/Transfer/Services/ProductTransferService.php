<?php

namespace Modules\Transfer\Services;

use App\Services\Upload\FileUploadService;
use App\Services\Upload\ImageUploadService;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Menu\Models\MenuTranslation;
use Modules\Product\Models\Product;
use Modules\Product\Models\Variation\ProductVariation;
use Modules\Product\Models\Variation\ProductVariationTranslation;
use Modules\Transfer\Jobs\ProcessProductTransferChunkJob;

class ProductTransferService
{
    private const SOURCE_LANGUAGE_ID = 3;
    private const SOURCE_STORE_ID = 0;
    private const TARGET_LOCALE = 'az';
    private const CHUNK_SIZE = 100;

    private const SOURCE_LANGUAGE_ID_BY_LOCALE = [
        'az' => 3,
        'en' => 8,
        'ru' => 9,
    ];

    private const TARGET_LANGUAGE_ID_BY_LOCALE = [
        'az' => 2,
        'en' => 1,
        'ru' => 3,
    ];

    public function __construct(
        private readonly ImageUploadService $imageUploadService,
        private readonly FileUploadService $fileUploadService,
        private readonly ProductFilterTransferService $productFilterTransferService
    ) {
    }

    public function preview(): array
    {
        $allProductIds = $this->sourceProductIds();
        $productIds = $allProductIds->take(50)->all();
        $products = $this->sourceProducts($productIds);

        return [
            'count' => $allProductIds->count(),
            'products' => $products->map(function (array $product): array {
                $leafCategory = $this->resolveLeafSourceCategory($product['category_ids']);

                return [
                    'product_id' => $product['product_id'],
                    'name' => $product['name'],
                    'slug' => $product['slug'],
                    'model' => $product['model'],
                    'sku' => $product['sku'],
                    'manufacturer_id' => $product['manufacturer_id'],
                    'manufacturer_name' => $product['manufacturer_name'],
                    'main_category_source_id' => $leafCategory['category_id'] ?? null,
                    'main_category_target_id' => isset($leafCategory['category_id'])
                        ? $this->resolveTargetMenuId((int) $leafCategory['category_id'])
                        : null,
                    'category_ids' => $product['category_ids'],
                    'gallery_count' => count($product['gallery']),
                    'filter_count' => count($product['filters']),
                    'image_exists' => $this->sourceFileExists($product['image']),
                    'status' => $product['status'],
                    'price' => $product['price'],
                    'special_price' => $product['special_price'],
                ];
            })->values()->all(),
        ];
    }

    public function dispatchImport(): array
    {
        $productIds = $this->sourceProductIds()->values();
        $jobsDispatched = 0;

        foreach ($productIds->chunk(self::CHUNK_SIZE) as $chunk) {
            ProcessProductTransferChunkJob::dispatch($chunk->values()->all())->onQueue('transfers');
            $jobsDispatched++;
        }

        return [
            'total_products' => $productIds->count(),
            'chunk_size' => self::CHUNK_SIZE,
            'jobs_dispatched' => $jobsDispatched,
        ];
    }

    public function processChunk(array $productIds): array
    {
        $products = $this->sourceProducts($productIds);

        $productCount = 0;
        $variationCount = 0;
        $filterRelationCount = 0;
        $menuFilterCount = 0;
        $mediaCount = 0;

        foreach ($products as $sourceProduct) {
            try {
                DB::transaction(function () use (
                    $sourceProduct,
                    &$productCount,
                    &$variationCount,
                    &$filterRelationCount,
                    &$menuFilterCount,
                    &$mediaCount
                ): void {
                    $resolvedCategoryIds = $this->resolveTargetCategoryIds($sourceProduct['category_ids']);
                    $mainCategoryId = $this->resolveMainCategoryId($sourceProduct['category_ids'], $resolvedCategoryIds);

                    $product = $this->resolveOrCreateProduct($sourceProduct, $mainCategoryId);
                    $productCount++;

                    $this->syncProductCategories($product, $resolvedCategoryIds);

                    $variation = $this->resolveOrCreateVariation($product, $sourceProduct);
                    $variationCount++;

                    $this->syncVariationTranslation($variation, $sourceProduct);

                    $mediaCount += $this->syncVariationMedia($variation, $sourceProduct);

                    $filterBindings = $this->resolveVariationFilterBindings($sourceProduct);

                    $variation->filterValues()->sync($filterBindings['filter_value_ids']);
                    $filterRelationCount += count($filterBindings['filter_value_ids']);

                    $menuIdsForFilterBinding = $this->resolveMenuIdsForMenuProductFilters(
                        $sourceProduct['category_ids'],
                        $resolvedCategoryIds,
                        $mainCategoryId
                    );

                    $menuFilterCount += $this->syncMenuProductFilters(
                        $menuIdsForFilterBinding,
                        $filterBindings['product_filter_ids']
                    );
                });
            } catch (\Throwable $throwable) {
                report($throwable);
            }
        }

        return [
            'products' => $productCount,
            'variations' => $variationCount,
            'filter_relations' => $filterRelationCount,
            'menu_filters' => $menuFilterCount,
            'media' => $mediaCount,
        ];
    }

    private function sourceProductIds(): Collection
    {
        return $this->baseProductQuery()
            ->pluck('p.product_id')
            ->map(fn ($productId): int => (int) $productId)
            ->unique()
            ->values();
    }

    private function sourceProducts(array $productIds): Collection
    {
        if ($productIds === []) {
            return collect();
        }

        $products = $this->baseProductDetailsQuery()
            ->whereIn('p.product_id', $productIds)
            ->orderBy('p.product_id')
            ->get();

        $categoryIdsByProduct = $this->sourceCategoryIdsByProductId($productIds);
        $galleryByProduct = $this->sourceGalleryByProductId($productIds);
        $specialPricesByProduct = $this->sourceSpecialPricesByProductId($productIds);
        $seoKeywordsByProduct = $this->sourceProductSeoKeywords($productIds);
        $translationsByProduct = $this->sourceProductTranslationsByProductId(
            $productIds,
            $seoKeywordsByProduct
        );
        $filtersByProduct = $this->sourceFiltersByProductId($productIds);

        return $products
            ->map(function (object $product) use (
                $categoryIdsByProduct,
                $galleryByProduct,
                $specialPricesByProduct,
                $translationsByProduct,
                $filtersByProduct
            ): ?array {
                $productId = (int) $product->product_id;
                $translations = $translationsByProduct->get($productId);

                if (!is_array($translations) || $translations === []) {
                    return null;
                }

                $primaryTranslation = $translations[self::TARGET_LOCALE];

                return [
                    'product_id' => $productId,
                    'model' => $this->nullIfEmpty($product->model),
                    'sku' => $this->nullIfEmpty($product->sku),
                    'stock' => max(0, (int) $product->quantity),
                    'image' => $this->nullIfEmpty($product->image),
                    'manufacturer_id' => (int) $product->manufacturer_id,
                    'manufacturer_name' => $this->nullIfEmpty($product->manufacturer_name),
                    'price' => (float) $product->price,
                    'special_price' => $specialPricesByProduct->get($productId),
                    'sort_order' => (int) $product->sort_order,
                    'status' => (int) $product->status === 1 ? 'Active' : 'Inactive',
                    'published_at' => $product->date_added ?: null,
                    'name' => $primaryTranslation['name'],
                    'description' => $primaryTranslation['description'],
                    'tags' => $this->extractTags($primaryTranslation['tags']),
                    'meta_title' => $primaryTranslation['meta_title'],
                    'meta_description' => $primaryTranslation['meta_description'],
                    'meta_keywords' => $primaryTranslation['meta_keywords'],
                    'slug' => $primaryTranslation['slug'],
                    'translations' => $translations,
                    'category_ids' => $categoryIdsByProduct->get($productId, []),
                    'gallery' => $galleryByProduct->get($productId, []),
                    'filters' => $filtersByProduct->get($productId, []),
                ];
            })
            ->filter()
            ->values();
    }

    private function baseProductQuery(): Builder
    {
        return DB::table('oc_product as p')
            ->join('oc_product_to_store as pts', function ($join): void {
                $join->on('pts.product_id', '=', 'p.product_id')
                    ->where('pts.store_id', '=', self::SOURCE_STORE_ID);
            })
            ->where('p.product_id', '>', 0);
    }

    private function baseProductDetailsQuery(): Builder
    {
        return $this->baseProductQuery()
            ->leftJoin('oc_manufacturer as m', 'm.manufacturer_id', '=', 'p.manufacturer_id')
            ->select([
                'p.product_id',
                'p.model',
                'p.sku',
                'p.quantity',
                'p.image',
                'p.manufacturer_id',
                'p.price',
                'p.sort_order',
                'p.status',
                'p.date_added',
                'p.date_modified',
                'm.name as manufacturer_name',
            ]);
    }

    private function sourceCategoryIdsByProductId(array $productIds): Collection
    {
        return DB::table('oc_product_to_category')
            ->whereIn('product_id', $productIds)
            ->select(['product_id', 'category_id'])
            ->orderBy('product_id')
            ->orderBy('category_id')
            ->get()
            ->groupBy('product_id')
            ->map(fn (Collection $items): array => $items->pluck('category_id')
                ->map(fn ($categoryId): int => (int) $categoryId)
                ->unique()
                ->values()
                ->all());
    }

    private function sourceGalleryByProductId(array $productIds): Collection
    {
        if (!Schema::hasTable('oc_product_image')) {
            return collect();
        }

        return DB::table('oc_product_image')
            ->whereIn('product_id', $productIds)
            ->select(['product_id', 'image', 'sort_order'])
            ->whereNotNull('image')
            ->orderBy('product_id')
            ->orderBy('sort_order')
            ->orderBy('product_image_id')
            ->get()
            ->groupBy('product_id')
            ->map(fn (Collection $items): array => $items->map(function (object $item): array {
                return [
                    'image' => $this->nullIfEmpty($item->image),
                    'sort_order' => (int) $item->sort_order,
                ];
            })->filter(fn (array $item): bool => $item['image'] !== null)->values()->all());
    }

    private function sourceSpecialPricesByProductId(array $productIds): Collection
    {
        if (!Schema::hasTable('oc_product_special')) {
            return collect();
        }

        return DB::table('oc_product_special')
            ->whereIn('product_id', $productIds)
            ->select(['product_id', 'price', 'priority'])
            ->orderBy('product_id')
            ->orderBy('priority')
            ->orderBy('product_special_id')
            ->get()
            ->groupBy('product_id')
            ->map(function (Collection $items): ?float {
                $first = $items->first();

                if ($first === null) {
                    return null;
                }

                return (float) $first->price;
            });
    }

    private function sourceProductSeoKeywords(array $productIds): Collection
    {
        if ($productIds === [] || !Schema::hasTable('oc_seo_url')) {
            return collect();
        }

        $queries = collect($productIds)
            ->map(fn (int $productId): string => 'product_id=' . $productId)
            ->values()
            ->all();

        $localeBySourceLanguageId = array_flip(
            self::SOURCE_LANGUAGE_ID_BY_LOCALE
        );

        return DB::table('oc_seo_url')
            ->where('store_id', self::SOURCE_STORE_ID)
            ->whereIn(
                'language_id',
                array_values(self::SOURCE_LANGUAGE_ID_BY_LOCALE)
            )
            ->whereIn('query', $queries)
            ->select([
                'query',
                'language_id',
                'keyword',
            ])
            ->orderBy('query')
            ->orderBy('language_id')
            ->get()
            ->groupBy(function (object $item): int {
                return (int) Str::after(
                    (string) $item->query,
                    'product_id='
                );
            })
            ->map(function (Collection $items) use (
                $localeBySourceLanguageId
            ): array {
                $keywords = [];

                foreach ($items as $item) {
                    $locale = $localeBySourceLanguageId[
                    (int) $item->language_id
                    ] ?? null;

                    if ($locale === null) {
                        continue;
                    }

                    $keyword = $this->nullIfEmpty($item->keyword);

                    if ($keyword !== null) {
                        $keywords[$locale] = $keyword;
                    }
                }

                return $keywords;
            });
    }

    private function sourceProductTranslationsByProductId(
        array $productIds,
        Collection $seoKeywordsByProduct
    ): Collection {
        $localeBySourceLanguageId = array_flip(
            self::SOURCE_LANGUAGE_ID_BY_LOCALE
        );

        return DB::table('oc_product_description')
            ->whereIn('product_id', $productIds)
            ->whereIn(
                'language_id',
                array_values(self::SOURCE_LANGUAGE_ID_BY_LOCALE)
            )
            ->select([
                'product_id',
                'language_id',
                'name',
                'description',
                'tag',
                'meta_title',
                'meta_description',
                'meta_keyword',
            ])
            ->orderBy('product_id')
            ->orderBy('language_id')
            ->get()
            ->groupBy('product_id')
            ->map(function (Collection $items, $productId) use (
                $localeBySourceLanguageId,
                $seoKeywordsByProduct
            ): array {
                $translations = [];

                foreach ($items as $item) {
                    $locale = $localeBySourceLanguageId[
                    (int) $item->language_id
                    ] ?? null;

                    if ($locale === null) {
                        continue;
                    }

                    $name = $this->nullIfEmpty($item->name);

                    if ($name === null) {
                        continue;
                    }

                    $translations[$locale] = [
                        'name' => $name,
                        'description' => $this->decodeHtml(
                            $this->nullIfEmpty($item->description)
                        ),
                        'tags' => $this->nullIfEmpty($item->tag),
                        'meta_title' => $this->nullIfEmpty(
                                $item->meta_title
                            ) ?? $name,
                        'meta_description' => $this->nullIfEmpty(
                            $item->meta_description
                        ),
                        'meta_keywords' => $this->nullIfEmpty(
                            $item->meta_keyword
                        ),
                        'slug' => $this->resolveProductSlug(
                            productId: (int) $productId,
                            locale: $locale,
                            name: $name,
                            seoKeywordsByProduct: $seoKeywordsByProduct
                        ),
                    ];
                }

                return $this->normalizeProductTranslations(
                    productId: (int) $productId,
                    translations: $translations
                );
            });
    }

    private function normalizeProductTranslations(
        int $productId,
        array $translations
    ): array {
        $primaryTranslation = $translations[self::TARGET_LOCALE]
            ?? collect($translations)->first();

        if (!is_array($primaryTranslation)) {
            return [];
        }

        foreach (array_keys(self::SOURCE_LANGUAGE_ID_BY_LOCALE) as $locale) {
            if (isset($translations[$locale])) {
                continue;
            }

            $fallbackName = $primaryTranslation['name'];
            $fallbackSlug = Str::slug($fallbackName);

            $translations[$locale] = [
                'name' => $fallbackName,
                'description' => $primaryTranslation['description'],
                'tags' => $primaryTranslation['tags'],
                'meta_title' => $primaryTranslation['meta_title']
                    ?? $fallbackName,
                'meta_description' => $primaryTranslation[
                'meta_description'
                ],
                'meta_keywords' => $primaryTranslation['meta_keywords'],
                'slug' => $fallbackSlug !== ''
                    ? $fallbackSlug
                    : 'product-' . $productId,
            ];
        }

        return collect(array_keys(self::SOURCE_LANGUAGE_ID_BY_LOCALE))
            ->mapWithKeys(fn (string $locale): array => [
                $locale => $translations[$locale],
            ])
            ->all();
    }

    private function sourceFiltersByProductId(array $productIds): Collection
    {
        return $this->productFilterTransferService->sourceFiltersByProductId($productIds);
    }

    private function sourceStandardFiltersByProductId(array $productIds): Collection
    {
        if (!Schema::hasTable('oc_product_filter') || !Schema::hasTable('oc_filter_description') || !Schema::hasTable('oc_filter_group_description')) {
            return collect();
        }

        return DB::table('oc_product_filter as pf')
            ->join('oc_filter as f', 'f.filter_id', '=', 'pf.filter_id')
            ->join('oc_filter_description as fd', function ($join): void {
                $join->on('fd.filter_id', '=', 'f.filter_id')
                    ->where('fd.language_id', '=', self::SOURCE_LANGUAGE_ID);
            })
            ->join('oc_filter_group_description as fgd', function ($join): void {
                $join->on('fgd.filter_group_id', '=', 'f.filter_group_id')
                    ->where('fgd.language_id', '=', self::SOURCE_LANGUAGE_ID);
            })
            ->whereIn('pf.product_id', $productIds)
            ->select([
                'pf.product_id',
                'f.filter_id',
                'f.sort_order',
                'fd.name as value_name',
                'fgd.name as filter_name',
            ])
            ->orderBy('pf.product_id')
            ->orderBy('f.filter_id')
            ->get()
            ->groupBy('product_id')
            ->map(fn (Collection $items): array => $items->map(function (object $item): array {
                return [
                    'source' => 'filter',
                    'source_id' => (int) $item->filter_id,
                    'filter_name' => $this->nullIfEmpty($item->filter_name),
                    'value_name' => $this->nullIfEmpty($item->value_name),
                    'sort_order' => (int) $item->sort_order,
                ];
            })->values()->all());
    }

    private function sourceOcFilterAttributeCacheByProductId(array $productIds): Collection
    {
        if (!Schema::hasTable('oc_ocfilter_attribute_cache') || !Schema::hasTable('oc_attribute_description')) {
            return collect();
        }

        return DB::table('oc_ocfilter_attribute_cache as cache')
            ->join('oc_attribute_description as ad', function ($join): void {
                $join->on('ad.attribute_id', '=', 'cache.attribute_id')
                    ->where('ad.language_id', '=', self::SOURCE_LANGUAGE_ID);
            })
            ->whereIn('cache.product_id', $productIds)
            ->where('cache.language_id', self::SOURCE_LANGUAGE_ID)
            ->select([
                'cache.product_id',
                'cache.attribute_id',
                'cache.text',
                'ad.name as filter_name',
            ])
            ->orderBy('cache.product_id')
            ->orderBy('cache.attribute_id')
            ->get()
            ->groupBy('product_id')
            ->map(fn (Collection $items): array => $items->map(function (object $item): array {
                return [
                    'source' => 'ocfilter_attribute_cache',
                    'source_id' => (int) $item->attribute_id,
                    'filter_name' => $this->nullIfEmpty($item->filter_name),
                    'value_name' => $this->nullIfEmpty($item->text),
                    'sort_order' => 0,
                ];
            })->values()->all());
    }

    private function sourceProductAttributesByProductId(array $productIds): Collection
    {
        if (!Schema::hasTable('oc_product_attribute') || !Schema::hasTable('oc_attribute_description')) {
            return collect();
        }

        return DB::table('oc_product_attribute as pa')
            ->join('oc_attribute_description as ad', function ($join): void {
                $join->on('ad.attribute_id', '=', 'pa.attribute_id')
                    ->where('ad.language_id', '=', self::SOURCE_LANGUAGE_ID);
            })
            ->whereIn('pa.product_id', $productIds)
            ->where('pa.language_id', self::SOURCE_LANGUAGE_ID)
            ->select([
                'pa.product_id',
                'pa.attribute_id',
                'pa.text',
                'ad.name as filter_name',
            ])
            ->orderBy('pa.product_id')
            ->orderBy('pa.attribute_id')
            ->get()
            ->groupBy('product_id')
            ->map(fn (Collection $items): array => $items->map(function (object $item): array {
                return [
                    'source' => 'product_attribute',
                    'source_id' => (int) $item->attribute_id,
                    'filter_name' => $this->nullIfEmpty($item->filter_name),
                    'value_name' => $this->nullIfEmpty($item->text),
                    'sort_order' => 0,
                ];
            })->values()->all());
    }

    private function expandSourceFilterItem(array $item): array
    {
        $rawFilterName = $this->nullIfEmpty($item['filter_name'] ?? null);
        $rawValueName = $this->nullIfEmpty($item['value_name'] ?? null);

        if ($rawFilterName === null || $rawValueName === null) {
            return [];
        }

        $displayFilterName = $this->canonicalFilterDisplayName($rawFilterName);
        $normalizedFilterName = $this->normalizeFilterName($rawFilterName);

        return collect($this->splitFilterValues($rawValueName, $normalizedFilterName))
            ->map(function (string $value) use ($item, $displayFilterName, $normalizedFilterName): array {
                return [
                    'source' => $item['source'] ?? 'unknown',
                    'source_id' => (int) ($item['source_id'] ?? 0),
                    'filter_name' => $displayFilterName,
                    'normalized_filter_name' => $normalizedFilterName,
                    'value_name' => $value,
                    'normalized_value_name' => $this->normalizeFilterValueName($value),
                    'sort_order' => (int) ($item['sort_order'] ?? 0),
                    'is_color_filter' => $this->isColorFilterName($displayFilterName),
                    'color_hex' => $this->resolveColorHex($value),
                ];
            })
            ->filter(fn (array $row): bool => $row['value_name'] !== '')
            ->values()
            ->all();
    }

    private function resolveOrCreateProduct(array $sourceProduct, ?int $mainCategoryId): Product
    {
        $existingProduct = $this->resolveExistingProduct($sourceProduct);
        $product = $existingProduct ?? new Product();

        if ($existingProduct !== null && method_exists($product, 'trashed') && $product->trashed()) {
            $product->restore();
        }

        $product->fill([
            'main_category_id' => $mainCategoryId,
            'status' => $sourceProduct['status'],
            'sort_order' => $sourceProduct['sort_order'],
            'tags' => $sourceProduct['tags'],
            'canonical_url' => $sourceProduct['slug'],
            'robots' => 'index,follow',
            'published_at' => $sourceProduct['published_at'],
        ]);
        $product->save();

        return $product;
    }

    private function resolveExistingProduct(array $sourceProduct): ?Product
    {
        $sku = $sourceProduct['sku'];
        $model = $sourceProduct['model'];

        if ($sku !== null) {
            $variation = ProductVariation::query()
                ->with('product')
                ->where('sku', $sku)
                ->first();

            if ($variation !== null && $variation->product !== null) {
                return $variation->product;
            }
        }

        if ($model !== null) {
            $variation = ProductVariation::query()
                ->with('product')
                ->where('model', $model)
                ->first();

            if ($variation !== null && $variation->product !== null) {
                return $variation->product;
            }
        }

        foreach (self::TARGET_LANGUAGE_ID_BY_LOCALE as $locale => $languageId) {
            $slug = $this->nullIfEmpty(
                data_get($sourceProduct, "translations.{$locale}.slug")
            );

            if ($slug === null) {
                continue;
            }

            $translation = ProductVariationTranslation::query()
                ->with('variation.product')
                ->where('language_id', $languageId)
                ->where('slug', $slug)
                ->first();

            if ($translation?->variation?->product !== null) {
                return $translation->variation->product;
            }
        }

        $azerbaijaniSlug = $this->nullIfEmpty(
            data_get($sourceProduct, 'translations.az.slug')
        );

        if ($azerbaijaniSlug === null) {
            return null;
        }

        $azerbaijaniName = $this->nullIfEmpty(
            data_get($sourceProduct, 'translations.az.name')
        );

        $legacyTranslation = ProductVariationTranslation::query()
            ->with('variation.product')
            ->where(
                'language_id',
                self::TARGET_LANGUAGE_ID_BY_LOCALE['en']
            )
            ->where('slug', $azerbaijaniSlug)
            ->when(
                $azerbaijaniName !== null,
                fn ($query) => $query->where('name', $azerbaijaniName)
            )
            ->first();

        return $legacyTranslation?->variation?->product;
    }

    private function syncProductCategories(Product $product, array $categoryIds): void
    {
        $product->categories()->sync($categoryIds);
    }

    private function resolveOrCreateVariation(Product $product, array $sourceProduct): ProductVariation
    {
        $variation = $product->variations()->first();

        if ($variation === null) {
            $variation = new ProductVariation();
            $variation->product_id = $product->id;
        }

        $basePrice = isset($sourceProduct['price']) ? (float) $sourceProduct['price'] : 0;
        $specialPrice = isset($sourceProduct['special_price']) && $sourceProduct['special_price'] !== null
            ? (float) $sourceProduct['special_price']
            : null;

        $hasDiscount = $specialPrice !== null && $specialPrice >= 0 && $specialPrice !== $basePrice;

        $variation->forceFill([
            'product_id' => $product->id,
            'sku' => $sourceProduct['sku'],
            'model' => $sourceProduct['model'],
            'stock' => $sourceProduct['stock'],
            'price' => $hasDiscount ? $specialPrice : $basePrice,
            'old_price' => $hasDiscount ? $basePrice : null,
            'discount_price' => null,
            'sort_order' => 0,
        ]);
        $variation->save();

        return $variation;
    }

    private function syncVariationTranslation(
        ProductVariation $variation,
        array $sourceProduct
    ): void {
        foreach (
            self::TARGET_LANGUAGE_ID_BY_LOCALE
            as $locale => $targetLanguageId
        ) {
            $translation = data_get(
                $sourceProduct,
                "translations.{$locale}"
            );

            if (!is_array($translation)) {
                continue;
            }

            $name = $this->nullIfEmpty($translation['name'] ?? null);

            if ($name === null) {
                continue;
            }

            $slug = $this->nullIfEmpty($translation['slug'] ?? null)
                ?? Str::slug($name);

            ProductVariationTranslation::query()->updateOrCreate(
                [
                    'product_variation_id' => $variation->id,
                    'language_id' => $targetLanguageId,
                ],
                [
                    'name' => $name,
                    'slug' => $this->ensureUniqueVariationSlug(
                        slug: $slug,
                        variationId: (int) $variation->id,
                        languageId: $targetLanguageId
                    ),
                    'description' => (string) (
                        $translation['description'] ?? ''
                    ),
                    'meta_title' => $this->nullIfEmpty(
                            $translation['meta_title'] ?? null
                        ) ?? $name,
                    'meta_description' => $this->nullIfEmpty(
                        $translation['meta_description'] ?? null
                    ),
                    'meta_keywords' => $this->nullIfEmpty(
                        $translation['meta_keywords'] ?? null
                    ),
                ]
            );
        }
    }

    private function syncVariationMedia(ProductVariation $variation, array $sourceProduct): int
    {
        DB::table('product_variation_media')
            ->where('product_variation_id', $variation->id)
            ->delete();

        $rows = [];
        $sortOrder = 0;
        $usedPaths = [];

        $mainImagePath = $this->storeSourceMedia($sourceProduct['image'], 'products/' . $variation->id);

        if ($mainImagePath !== null) {
            $usedPaths[$mainImagePath] = true;

            $rows[] = [
                'product_variation_id' => $variation->id,
                'type' => 'image',
                'path' => $mainImagePath,
                'is_main' => true,
                'sort_order' => $sortOrder++,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach ($sourceProduct['gallery'] as $galleryItem) {
            $galleryPath = $this->storeSourceMedia($galleryItem['image'], 'products/' . $variation->id . '/gallery');

            if ($galleryPath === null || isset($usedPaths[$galleryPath])) {
                continue;
            }

            $usedPaths[$galleryPath] = true;

            $rows[] = [
                'product_variation_id' => $variation->id,
                'type' => 'image',
                'path' => $galleryPath,
                'is_main' => false,
                'sort_order' => $sortOrder++,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rows !== []) {
            DB::table('product_variation_media')->insert($rows);
        }

        return count($rows);
    }

    private function resolveVariationFilterBindings(array $sourceProduct): array
    {
        $filterValueIds = [];
        $productFilterIds = [];

        if ($sourceProduct['manufacturer_name'] !== null) {
            $manufacturerName = (string) $sourceProduct['manufacturer_name'];

            $brandBinding = $this->productFilterTransferService->resolveOrCreateFilterValueBinding(
                filterTranslations: $this->productFilterTransferService->brandFilterTranslations(),
                valueTranslations: [
                    'az' => $manufacturerName,
                    'en' => $manufacturerName,
                    'ru' => $manufacturerName,
                ],
                sortOrder: 0,
                isColorFilter: false,
                colorHex: null
            );

            $productFilterIds[] = (int) $brandBinding['filter']->id;
            $filterValueIds[] = (int) $brandBinding['value']->id;
        }

        foreach ($sourceProduct['filters'] as $sourceFilter) {
            $filterName = $this->nullIfEmpty($sourceFilter['filter_name'] ?? null);
            $valueName = $this->nullIfEmpty($sourceFilter['value_name'] ?? null);

            if ($filterName === null || $valueName === null) {
                continue;
            }

            $filterTranslations = [];
            $valueTranslations = [];

            foreach (['az', 'en', 'ru'] as $locale) {
                $filterTranslations[$locale] = $this->nullIfEmpty(
                    data_get($sourceFilter, "translations.{$locale}.filter_name")
                ) ?? $filterName;

                $valueTranslations[$locale] = $this->nullIfEmpty(
                    data_get($sourceFilter, "translations.{$locale}.value_name")
                ) ?? $valueName;
            }

            $binding = $this->productFilterTransferService->resolveOrCreateFilterValueBinding(
                filterTranslations: $filterTranslations,
                valueTranslations: $valueTranslations,
                sortOrder: (int) ($sourceFilter['sort_order'] ?? 0),
                isColorFilter: (bool) ($sourceFilter['is_color_filter'] ?? false),
                colorHex: $sourceFilter['color_hex'] ?? null
            );

            $productFilterIds[] = (int) $binding['filter']->id;
            $filterValueIds[] = (int) $binding['value']->id;
        }

        return [
            'filter_value_ids' => array_values(array_unique($filterValueIds)),
            'product_filter_ids' => array_values(array_unique($productFilterIds)),
        ];
    }

    private function syncMenuProductFilters(array $menuIds, array $productFilterIds): int
    {
        if ($menuIds === [] || $productFilterIds === [] || !Schema::hasTable('menu_product_filters')) {
            return 0;
        }

        $existingPairs = DB::table('menu_product_filters')
            ->whereIn('menu_id', $menuIds)
            ->whereIn('product_filter_id', $productFilterIds)
            ->get(['menu_id', 'product_filter_id'])
            ->map(fn ($row): string => $row->menu_id . ':' . $row->product_filter_id)
            ->all();

        $existingLookup = array_fill_keys($existingPairs, true);
        $rows = [];
        $now = now();
        $affectedCount = 0;

        foreach ($menuIds as $menuId) {
            foreach ($productFilterIds as $productFilterId) {
                $pairKey = $menuId . ':' . $productFilterId;

                if (isset($existingLookup[$pairKey])) {
                    DB::table('menu_product_filters')
                        ->where('menu_id', $menuId)
                        ->where('product_filter_id', $productFilterId)
                        ->update([
                            'is_active' => 1,
                            'updated_at' => $now,
                        ]);

                    $affectedCount++;
                    continue;
                }

                $rows[] = [
                    'menu_id' => $menuId,
                    'product_filter_id' => $productFilterId,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $affectedCount++;
            }
        }

        if ($rows !== []) {
            DB::table('menu_product_filters')->insert($rows);
        }

        return $affectedCount;
    }

    private function resolveTargetCategoryIds(array $sourceCategoryIds): array
    {
        return collect($sourceCategoryIds)
            ->map(fn (int $categoryId): ?int => $this->resolveTargetMenuId($categoryId))
            ->filter(fn (?int $menuId): bool => $menuId !== null)
            ->unique()
            ->values()
            ->all();
    }

    private function resolveMainCategoryId(array $sourceCategoryIds, array $targetCategoryIds): ?int
    {
        if ($sourceCategoryIds === [] || $targetCategoryIds === []) {
            return $targetCategoryIds[0] ?? null;
        }

        $leafCategory = $this->resolveLeafSourceCategory($sourceCategoryIds);

        if ($leafCategory !== null) {
            $resolvedLeafMenuId = $this->resolveTargetMenuId((int) $leafCategory['category_id']);

            if ($resolvedLeafMenuId !== null) {
                return $resolvedLeafMenuId;
            }
        }

        return $targetCategoryIds[0] ?? null;
    }

    private function resolveMenuIdsForMenuProductFilters(
        array $sourceCategoryIds,
        array $resolvedCategoryIds,
        ?int $mainCategoryId
    ): array {
        $menuIds = [];

        if ($mainCategoryId !== null) {
            $menuIds[] = $mainCategoryId;
        }

        $leafCategory = $this->resolveLeafSourceCategory($sourceCategoryIds);

        if ($leafCategory !== null) {
            $leafMenuId = $this->resolveTargetMenuId((int) $leafCategory['category_id']);

            if ($leafMenuId !== null) {
                $menuIds[] = $leafMenuId;
            }
        }

        foreach ($resolvedCategoryIds as $resolvedCategoryId) {
            $menuIds[] = (int) $resolvedCategoryId;
        }

        return collect($menuIds)
            ->filter(fn ($menuId): bool => $menuId !== null && (int) $menuId > 0)
            ->map(fn ($menuId): int => (int) $menuId)
            ->unique()
            ->values()
            ->all();
    }

    private function resolveLeafSourceCategory(array $categoryIds): ?array
    {
        if ($categoryIds === []) {
            return null;
        }

        $categories = DB::table('oc_category as c')
            ->leftJoinSub(
                DB::table('oc_category_path')
                    ->selectRaw('category_id, MAX(level) as level')
                    ->groupBy('category_id'),
                'cp',
                function ($join): void {
                    $join->on('cp.category_id', '=', 'c.category_id');
                }
            )
            ->whereIn('c.category_id', $categoryIds)
            ->select([
                'c.category_id',
                'c.parent_id',
                DB::raw('COALESCE(cp.level, 0) as level'),
            ])
            ->get();

        if ($categories->isEmpty()) {
            return null;
        }

        $parentIds = $categories->pluck('parent_id')->map(fn ($id): int => (int) $id)->all();
        $leafCandidates = $categories->filter(fn (object $category): bool => !in_array((int) $category->category_id, $parentIds, true));

        $candidate = $leafCandidates
            ->sortByDesc(fn (object $category): int => (int) $category->level)
            ->first();

        if ($candidate !== null) {
            return [
                'category_id' => (int) $candidate->category_id,
                'parent_id' => (int) $candidate->parent_id,
                'level' => (int) $candidate->level,
            ];
        }

        $fallback = $categories
            ->sortByDesc(fn (object $category): int => (int) $category->level)
            ->first();

        if ($fallback === null) {
            return null;
        }

        return [
            'category_id' => (int) $fallback->category_id,
            'parent_id' => (int) $fallback->parent_id,
            'level' => (int) $fallback->level,
        ];
    }

    private function resolveTargetMenuId(int $sourceCategoryId): ?int
    {
        $slug = $this->findCategoryKeyword($sourceCategoryId);

        if ($slug !== null) {
            $translation = MenuTranslation::query()
                ->where('locale', self::TARGET_LOCALE)
                ->whereRaw('LOWER(TRIM(link)) = ?', [mb_strtolower(trim($slug))])
                ->whereHas('menu', function ($query): void {
                    $query->where('type', 'categories');
                })
                ->first();

            if ($translation !== null) {
                return (int) $translation->menu_id;
            }
        }

        $sourceName = DB::table('oc_category_description')
            ->where('category_id', $sourceCategoryId)
            ->where('language_id', self::SOURCE_LANGUAGE_ID)
            ->value('name');

        $sourceName = $this->nullIfEmpty($sourceName);

        if ($sourceName === null) {
            return null;
        }

        $translation = MenuTranslation::query()
            ->where('locale', self::TARGET_LOCALE)
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($sourceName))])
            ->whereHas('menu', function ($query): void {
                $query->where('type', 'categories');
            })
            ->first();

        return $translation?->menu_id !== null ? (int) $translation->menu_id : null;
    }

    private function findCategoryKeyword(int $categoryId): ?string
    {
        $row = DB::table('oc_seo_url')
            ->where('language_id', self::SOURCE_LANGUAGE_ID)
            ->where('store_id', self::SOURCE_STORE_ID)
            ->where('query', 'category_id=' . $categoryId)
            ->select(['keyword'])
            ->first();

        if ($row === null || !is_string($row->keyword)) {
            return null;
        }

        $keyword = trim($row->keyword);

        return $keyword === '' ? null : $keyword;
    }

    private function resolveProductSlug(
        int $productId,
        string $locale,
        string $name,
        Collection $seoKeywordsByProduct
    ): string {
        $keyword = $this->nullIfEmpty(
            data_get(
                $seoKeywordsByProduct->get($productId, []),
                $locale
            )
        );

        if ($keyword !== null) {
            return $keyword;
        }

        $slug = Str::slug($name);

        return $slug !== '' ? $slug : 'product-' . $productId;
    }

    private function ensureUniqueVariationSlug(
        string $slug,
        int $variationId,
        int $languageId
    ): string {
        $baseSlug = $slug !== '' ? $slug : 'variation';
        $candidate = $baseSlug;
        $suffix = 1;

        while (
        ProductVariationTranslation::query()
            ->where('language_id', $languageId)
            ->where('slug', $candidate)
            ->where('product_variation_id', '!=', $variationId)
            ->exists()
        ) {
            $candidate = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function storeSourceMedia(?string $relativePath, string $directory): ?string
    {
        if ($relativePath === null || trim($relativePath) === '') {
            return null;
        }

        $fullPath = $this->sourceAbsolutePath($relativePath);

        if (!File::exists($fullPath) || !File::isFile($fullPath)) {
            return null;
        }

        $uploadedFile = new UploadedFile(
            $fullPath,
            basename($fullPath),
            File::mimeType($fullPath) ?: 'application/octet-stream',
            null,
            true
        );

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $allowedImageExtensions = ['png', 'jpeg', 'jpg', 'gif', 'bmp', 'ico', 'tiff', 'tif', 'svg', 'webp'];

        if (!in_array($extension, $allowedImageExtensions, true)) {
            return $this->fileUploadService->storeRaw($uploadedFile, $directory);
        }

        try {
            if (in_array($extension, ['svg', 'ico'], true)) {
                return $this->fileUploadService->storeRaw($uploadedFile, $directory);
            }

            return $this->imageUploadService->uploadImage($uploadedFile, $directory, 'image');
        } catch (\Throwable) {
            return $this->fileUploadService->storeRaw($uploadedFile, $directory);
        }
    }

    private function normalizeFilterName(string $name): string
    {
        $normalized = mb_strtolower(trim($name));
        $normalized = str_replace(['İ', 'I', 'ı'], ['i', 'i', 'i'], $normalized);
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;
        $normalized = str_replace(['/'], ' ', $normalized);
        $normalized = preg_replace('/[^\p{L}\p{N}\s\.\-]+/u', '', $normalized) ?? $normalized;
        $normalized = trim($normalized);

        $aliases = [
            'reng' => 'rəng',
            'rəng' => 'rəng',
            'rəngi' => 'rəng',
            'renk' => 'rəng',
            'color' => 'rəng',
            'colour' => 'rəng',
            'cvet' => 'rəng',
            'цвет' => 'rəng',
            'olcu' => 'ölçü',
            'ölçü' => 'ölçü',
            'olcusu' => 'ölçü',
            'ölçüsü' => 'ölçü',
            'size' => 'ölçü',
            'razmer' => 'ölçü',
            'diametr' => 'diametr',
            'diameter' => 'diametr',
            'çap' => 'diametr',
            'cap' => 'diametr',
            'material' => 'material',
            'materyal' => 'material',
            'materal' => 'material',
            'istehsal olkesi' => 'istehsal ölkəsi',
            'istehsal ölkəsi' => 'istehsal ölkəsi',
            'mense olkesi' => 'istehsal ölkəsi',
            'mənşə ölkəsi' => 'istehsal ölkəsi',
            'country of origin' => 'istehsal ölkəsi',
            'origin country' => 'istehsal ölkəsi',
            'country' => 'istehsal ölkəsi',
            'ölkə' => 'istehsal ölkəsi',
            'olke' => 'istehsal ölkəsi',
            'i.k' => 'i.k',
            'ik' => 'i.k',
            'code' => 'kod',
            'kod' => 'kod',
            'artikul' => 'kod',
            'weight' => 'çəki',
            'ceki' => 'çəki',
            'çəki' => 'çəki',
            'uzunluq' => 'uzunluq',
            'length' => 'uzunluq',
            'hündürlük' => 'hündürlük',
            'hundurluk' => 'hündürlük',
            'height' => 'hündürlük',
            'en' => 'en',
            'width' => 'en',
            'güc' => 'güc',
            'guc' => 'güc',
            'guc w' => 'güc',
            'güc w' => 'güc',
            'power' => 'güc',
            'disk diametri' => 'disk diametri',
            'disk diameter' => 'disk diametri',
            'maksimal dayanaqliliq atm' => 'maksimal dayanaqlılıq',
            'maksimal dayanaqlılıq atm' => 'maksimal dayanaqlılıq',
            'maksimal dayanaqliliq' => 'maksimal dayanaqlılıq',
            'maksimal dayanaqlılıq' => 'maksimal dayanaqlılıq',
        ];

        return $aliases[$normalized] ?? $normalized;
    }

    private function canonicalFilterDisplayName(string $name): string
    {
        return match ($this->normalizeFilterName($name)) {
            'rəng' => 'Rəng',
            'ölçü' => 'Ölçü',
            'diametr' => 'Diametr',
            'material' => 'Material',
            'istehsal ölkəsi' => 'İstehsal ölkəsi',
            'i.k' => 'İ.K',
            'kod' => 'Kod',
            'çəki' => 'Çəki',
            'uzunluq' => 'Uzunluq',
            'hündürlük' => 'Hündürlük',
            'en' => 'En',
            'güc' => 'Güc',
            'disk diametri' => 'Disk Diametri',
            'maksimal dayanaqlılıq' => 'Maksimal Dayanaqlılıq',
            default => $this->titleCaseAz($name),
        };
    }

    private function normalizeFilterValueName(string $value): string
    {
        $normalized = mb_strtolower(trim($value));
        $normalized = str_replace(['İ', 'I', 'ı'], ['i', 'i', 'i'], $normalized);
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;

        return trim($normalized);
    }

    private function splitFilterValues(string $value, string $normalizedFilterName): array
    {
        $value = trim($value);

        if ($value === '') {
            return [];
        }

        if (in_array($normalizedFilterName, ['güc', 'diametr', 'disk diametri', 'çəki', 'uzunluq', 'hündürlük', 'en', 'maksimal dayanaqlılıq', 'i.k'], true)) {
            return [$value];
        }

        if (str_contains($value, "\n")) {
            return collect(preg_split('/\r\n|\r|\n/u', $value) ?: [])
                ->map(fn ($item): string => trim((string) $item))
                ->filter()
                ->values()
                ->all();
        }

        $parts = preg_split('/\s*[,;|]+\s*/u', $value) ?: [$value];

        return collect($parts)
            ->map(fn ($item): string => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    private function isColorFilterName(string $name): bool
    {
        return $this->normalizeFilterName($name) === 'rəng';
    }

    private function resolveColorHex(?string $value): ?string
    {
        $value = $this->nullIfEmpty($value);

        if ($value === null) {
            return null;
        }

        if (preg_match('/#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})\b/', $value, $matches) === 1) {
            return $this->normalizeHexColor($matches[0]);
        }

        $normalized = $this->normalizeFilterValueName($value);

        $map = [
            'ağ' => '#FFFFFF',
            'ag' => '#FFFFFF',
            'white' => '#FFFFFF',
            'bəyaz' => '#FFFFFF',
            'qara' => '#000000',
            'black' => '#000000',
            'boz' => '#808080',
            'gray' => '#808080',
            'grey' => '#808080',
            'gri' => '#808080',
            'gümüşü' => '#C0C0C0',
            'gumusu' => '#C0C0C0',
            'silver' => '#C0C0C0',
            'xrom' => '#B0B7C3',
            'chrome' => '#B0B7C3',
            'qırmızı' => '#FF0000',
            'qirmizi' => '#FF0000',
            'red' => '#FF0000',
            'mavi' => '#0000FF',
            'blue' => '#0000FF',
            'göy' => '#0000FF',
            'goy' => '#0000FF',
            'yaşıl' => '#008000',
            'yasil' => '#008000',
            'green' => '#008000',
            'sarı' => '#FFFF00',
            'sari' => '#FFFF00',
            'yellow' => '#FFFF00',
            'narıncı' => '#FFA500',
            'narinci' => '#FFA500',
            'orange' => '#FFA500',
            'çəhrayı' => '#FFC0CB',
            'cehrayi' => '#FFC0CB',
            'pink' => '#FFC0CB',
            'bənövşəyi' => '#800080',
            'benovseyi' => '#800080',
            'purple' => '#800080',
            'qəhvəyi' => '#8B4513',
            'qehveyi' => '#8B4513',
            'brown' => '#8B4513',
            'bej' => '#F5F5DC',
            'beige' => '#F5F5DC',
            'latun' => '#C9A227',
            'gold' => '#FFD700',
            'qızılı' => '#FFD700',
            'qizili' => '#FFD700',
        ];

        return $map[$normalized] ?? null;
    }

    private function normalizeHexColor(?string $hex): ?string
    {
        $hex = $this->nullIfEmpty($hex);

        if ($hex === null) {
            return null;
        }

        $hex = ltrim($hex, '#');

        if (preg_match('/^[A-Fa-f0-9]{3}$/', $hex) === 1) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (preg_match('/^[A-Fa-f0-9]{6}$/', $hex) !== 1) {
            return null;
        }

        return '#' . strtoupper($hex);
    }

    private function titleCaseAz(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $words = preg_split('/\s+/u', $value) ?: [$value];

        return collect($words)
            ->map(function (string $word): string {
                $first = mb_substr($word, 0, 1);
                $rest = mb_substr($word, 1);

                return mb_strtoupper($first) . mb_strtolower($rest);
            })
            ->implode(' ');
    }

    private function sourceAbsolutePath(string $relativePath): string
    {
        return public_path('uploads/opencart/' . ltrim($relativePath, '/'));
    }

    private function sourceFileExists(?string $relativePath): bool
    {
        if ($relativePath === null || trim((string) $relativePath) === '') {
            return false;
        }

        return File::exists($this->sourceAbsolutePath($relativePath));
    }

    private function extractTags(mixed $value): array
    {
        $value = $this->nullIfEmpty($value);

        if ($value === null) {
            return [];
        }

        return collect(explode(',', $value))
            ->map(fn ($item): string => trim((string) $item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function decodeHtml(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
