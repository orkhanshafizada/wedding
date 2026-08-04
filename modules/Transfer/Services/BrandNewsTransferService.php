<?php

namespace Modules\Transfer\Services;

use App\Services\Upload\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Grids\Models\Grid;
use Modules\Grids\Models\GridMedia;
use Modules\Menu\Models\Menu;
use Modules\Menu\Models\MenuTranslation;
use Modules\Product\Models\Variation\ProductVariation;
use Modules\Product\Models\Variation\ProductVariationTranslation;

class BrandNewsTransferService
{
    private const SOURCE_STORE_ID = 0;
    private const MENU_VIEW_TYPE = 'brand-news';
    private const MENU_SLUG = 'brand-news';
    private const HTTP_TIMEOUT_SECONDS = 20;

    private const SOURCE_LANGUAGE_ID_BY_LOCALE = [
        'az' => 3,
        'en' => 8,
        'ru' => 9,
    ];

    private const MENU_TRANSLATIONS = [
        'az' => [
            'name' => 'Brand Xeberler',
            'slug' => 'brand-news',
            'meta_title' => 'Brand Xeberler',
            'meta_description' => 'Brand Xeberler',
            'meta_keywords' => 'Brand Xeberler',
        ],
        'en' => [
            'name' => 'Brand News',
            'slug' => 'brand-news',
            'meta_title' => 'Brand News',
            'meta_description' => 'Brand News',
            'meta_keywords' => 'Brand News',
        ],
        'ru' => [
            'name' => 'Новости брендов',
            'slug' => 'brand-news',
            'meta_title' => 'Новости брендов',
            'meta_description' => 'Новости брендов',
            'meta_keywords' => 'Новости брендов',
        ],
    ];

    private const BRAND_FILTER_KEYWORDS = [
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

    private array $uploadedRemoteAssets = [];

    public function __construct(
        private readonly FileUploadService $fileUploadService
    ) {
    }

    public function preview(): array
    {
        $partners = $this->sourcePartners();

        return [
            'count' => $partners->count(),
            'language_count' => count(self::SOURCE_LANGUAGE_ID_BY_LOCALE),
            'items' => $partners->take(30)->map(function (array $partner): array {
                $relatedProducts = $this->resolveRelatedProducts((int) $partner['manufacturer_id']);

                return [
                    'partner_id' => (int) $partner['partner_id'],
                    'name' => (string) ($partner['descriptions']['az']['name'] ?? $partner['base_name'] ?? ''),
                    'az_name' => (string) ($partner['descriptions']['az']['name'] ?? ''),
                    'en_name' => (string) ($partner['descriptions']['en']['name'] ?? ''),
                    'ru_name' => (string) ($partner['descriptions']['ru']['name'] ?? ''),
                    'manufacturer_id' => (int) $partner['manufacturer_id'],
                    'status' => (bool) $partner['status'],
                    'sort_order' => (int) $partner['sort_order'],
                    'image_exists' => $this->sourceAssetExists($partner['image']),
                    'banner_exists' => $this->sourceAssetExists($partner['banner']),
                    'related_products_count' => count($relatedProducts),
                    'az_meta_title' => (string) ($partner['descriptions']['az']['meta_title'] ?? ''),
                    'en_meta_title' => (string) ($partner['descriptions']['en']['meta_title'] ?? ''),
                    'ru_meta_title' => (string) ($partner['descriptions']['ru']['meta_title'] ?? ''),
                ];
            })->values()->all(),
        ];
    }

    public function import(): array
    {
        $partners = $this->sourcePartners();

        return DB::transaction(function () use ($partners): array {
            $menu = $this->resolveOrCreateBrandNewsMenu();

            $gridCount = 0;
            $relatedProductCount = 0;

            foreach ($partners as $partner) {
                $grid = $this->resolveOrCreateGrid($menu, $partner);
                $relatedProducts = $this->resolveRelatedProducts((int) $partner['manufacturer_id']);

                $relatedProductCount += $this->syncRelatedProducts($grid, $relatedProducts);
                $gridCount++;
            }

            return [
                'menus' => 1,
                'grids' => $gridCount,
                'related_products' => $relatedProductCount,
            ];
        });
    }

    private function resolveOrCreateBrandNewsMenu(): Menu
    {
        $menu = $this->resolveExistingBrandNewsMenu() ?? new Menu();

        $menu->fill([
            'parent_id' => null,
            'type' => 'grids',
            'view_type' => self::MENU_VIEW_TYPE,
            'status' => true,
            'show_on_main_page' => false,
            'in_header' => false,
            'in_footer' => false,
            'icon' => null,
            'icon_image' => null,
            'main_image' => null,
            'text_color' => null,
            'bg_color' => null,
            'sort_order' => 0,
        ]);
        $menu->save();

        foreach (self::MENU_TRANSLATIONS as $locale => $translation) {
            MenuTranslation::query()->updateOrCreate(
                [
                    'menu_id' => $menu->id,
                    'locale' => $locale,
                ],
                [
                    'name' => $translation['name'],
                    'title' => $translation['name'],
                    'description' => null,
                    'link' => $translation['slug'],
                    'meta_title' => $translation['meta_title'],
                    'meta_description' => $translation['meta_description'],
                    'meta_keywords' => $translation['meta_keywords'],
                ]
            );
        }

        return $menu;
    }

    private function resolveExistingBrandNewsMenu(): ?Menu
    {
        $translation = MenuTranslation::query()
            ->whereIn('locale', array_keys(self::SOURCE_LANGUAGE_ID_BY_LOCALE))
            ->where(function ($query): void {
                $query->where('link', self::MENU_SLUG)
                    ->orWhere('name', self::MENU_TRANSLATIONS['az']['name'])
                    ->orWhere('name', self::MENU_TRANSLATIONS['en']['name'])
                    ->orWhere('name', self::MENU_TRANSLATIONS['ru']['name']);
            })
            ->whereHas('menu', function ($query): void {
                $query->where('type', 'grids')
                    ->where('view_type', self::MENU_VIEW_TYPE);
            })
            ->first();

        return $translation?->menu;
    }

    private function resolveOrCreateGrid(Menu $menu, array $partner): Grid
    {
        $payload = $this->buildGridPayload($partner);
        $grid = $this->resolveExistingGrid($menu, $partner, $payload) ?? new Grid();

        $bannerPath = $this->uploadSourceAsset(
            $partner['banner'],
            'grids/brand-news/' . (int) $partner['partner_id'] . '/banner'
        );

        $grid->fill([
            'menu_id' => $menu->id,
            'datetime1' => null,
            'datetime2' => null,
            'banner' => $bannerPath,
            'name' => $payload['name'],
            'slug' => $payload['slug'],
            'content' => $payload['content'],
            'location_or_group' => $payload['location_or_group'],
            'meta_title' => $payload['meta_title'],
            'meta_description' => $payload['meta_description'],
            'meta_keywords' => $payload['meta_keywords'],
            'is_active' => (bool) $partner['status'],
            'sort_order' => (int) $partner['sort_order'],
        ]);
        $grid->save();

        $this->syncGridMedia($grid, $partner);

        return $grid;
    }

    private function resolveExistingGrid(Menu $menu, array $partner, array $payload): ?Grid
    {
        $partnerId = (int) $partner['partner_id'];
        $azSlug = (string) ($payload['slug']['az'] ?? '');
        $azName = (string) ($payload['name']['az'] ?? '');

        if ($azSlug !== '') {
            $grid = Grid::query()
                ->where('menu_id', $menu->id)
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(slug, '$.\"az\"')) = ?", [$azSlug])
                ->first();

            if ($grid !== null) {
                return $grid;
            }
        }

        $legacySlugs = [
            'brand-news-' . $partnerId,
            'brand-news-' . $partnerId . '-az',
        ];

        foreach ($legacySlugs as $legacySlug) {
            $grid = Grid::query()
                ->where('menu_id', $menu->id)
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(slug, '$.\"az\"')) = ?", [$legacySlug])
                ->first();

            if ($grid !== null) {
                return $grid;
            }
        }

        if ($azName !== '') {
            $grid = Grid::query()
                ->where('menu_id', $menu->id)
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"az\"')) = ?", [$azName])
                ->first();

            if ($grid !== null) {
                return $grid;
            }
        }

        return null;
    }

    private function buildGridPayload(array $partner): array
    {
        $payload = [
            'name' => [],
            'slug' => [],
            'content' => [],
            'location_or_group' => [],
            'meta_title' => [],
            'meta_description' => [],
            'meta_keywords' => [],
        ];

        foreach (self::SOURCE_LANGUAGE_ID_BY_LOCALE as $locale => $sourceLanguageId) {
            $description = $partner['descriptions'][$locale] ?? [
                'name' => '',
                'text' => '',
                'meta_title' => '',
                'meta_description' => '',
                'meta_keyword' => '',
            ];

            $name = $this->nullIfEmpty($description['name'] ?? null) ?? '';
            $content = $this->nullIfEmpty($description['text'] ?? null) ?? '';
            $metaTitle = $this->nullIfEmpty($description['meta_title'] ?? null) ?? '';
            $metaDescription = $this->nullIfEmpty($description['meta_description'] ?? null) ?? '';
            $metaKeywords = $this->nullIfEmpty($description['meta_keyword'] ?? null) ?? '';

            if ($locale === 'az' && $name === '') {
                $name = $this->nullIfEmpty($partner['base_name']) ?? 'Brand News #' . (int) $partner['partner_id'];
            }

            if ($locale === 'az' && $content === '') {
                $content = $this->nullIfEmpty($partner['base_text']) ?? '';
            }

            $content = $this->replaceRemoteImagesInHtml(
                html: $this->decodeHtml($content),
                directory: 'grids/brand-news/' . (int) $partner['partner_id'] . '/content/' . $locale
            );

            $payload['name'][$locale] = $name;
            $payload['slug'][$locale] = $this->resolvePartnerSlug((int) $partner['partner_id'], $name, $locale, $sourceLanguageId);
            $payload['content'][$locale] = $content;
            $payload['location_or_group'][$locale] = $this->nullIfEmpty($partner['site']) ?? '';
            $payload['meta_title'][$locale] = $metaTitle !== '' ? $metaTitle : $name;
            $payload['meta_description'][$locale] = $metaDescription;
            $payload['meta_keywords'][$locale] = $metaKeywords;
        }

        return $payload;
    }

    private function syncGridMedia(Grid $grid, array $partner): void
    {
        $existingMedia = GridMedia::query()
            ->where('grid_id', $grid->id)
            ->get();

        foreach ($existingMedia as $media) {
            $path = (string) $media->path;

            if ($path !== '' && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            $media->delete();
        }

        $imagePath = $this->uploadSourceAsset(
            $partner['image'],
            'grids/brand-news/' . (int) $partner['partner_id'] . '/gallery'
        );

        if ($imagePath === null) {
            return;
        }

        GridMedia::query()->create([
            'grid_id' => $grid->id,
            'type' => 'image',
            'path' => $imagePath,
            'original_name' => basename($imagePath),
            'is_main' => true,
            'sort_order' => 0,
        ]);
    }

    private function syncRelatedProducts(Grid $grid, array $relatedProducts): int
    {
        DB::table('grids_related_products')
            ->where('grid_id', $grid->id)
            ->delete();

        if ($relatedProducts === []) {
            return 0;
        }

        $rows = [];
        $usedProductIds = [];
        $usedVariationIds = [];
        $sortOrder = 0;

        foreach ($relatedProducts as $relatedProduct) {
            $productId = (int) $relatedProduct['product_id'];
            $productVariationId = (int) $relatedProduct['product_variation_id'];

            if ($productId <= 0 || $productVariationId <= 0) {
                continue;
            }

            if (isset($usedProductIds[$productId]) || isset($usedVariationIds[$productVariationId])) {
                continue;
            }

            $usedProductIds[$productId] = true;
            $usedVariationIds[$productVariationId] = true;

            $rows[] = [
                'grid_id' => $grid->id,
                'product_id' => $productId,
                'product_variation_id' => $productVariationId,
                'sort_order' => $sortOrder++,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rows === []) {
            return 0;
        }

        DB::table('grids_related_products')->insert($rows);

        return count($rows);
    }

    private function resolveRelatedProducts(int $manufacturerId): array
    {
        if ($manufacturerId <= 0) {
            return [];
        }

        $matches = [];

        foreach ($this->resolveProductsFromSourceManufacturer($manufacturerId) as $match) {
            $matches[$match['product_id'] . ':' . $match['product_variation_id']] = $match;
        }

        foreach ($this->resolveProductsFromBrandFilterValue($manufacturerId) as $match) {
            $matches[$match['product_id'] . ':' . $match['product_variation_id']] = $match;
        }

        foreach ($this->resolveProductsFromBrandMenu($manufacturerId) as $match) {
            $matches[$match['product_id'] . ':' . $match['product_variation_id']] = $match;
        }

        return array_values($matches);
    }

    private function resolveProductsFromSourceManufacturer(int $manufacturerId): array
    {
        $sourceProducts = DB::table('oc_product as p')
            ->leftJoin('oc_product_description as pd', function ($join): void {
                $join->on('pd.product_id', '=', 'p.product_id')
                    ->where('pd.language_id', '=', self::SOURCE_LANGUAGE_ID_BY_LOCALE['az']);
            })
            ->leftJoin('oc_seo_url as su', function ($join): void {
                $join->on('su.query', '=', DB::raw("CONCAT('product_id=', p.product_id)"))
                    ->where('su.language_id', '=', self::SOURCE_LANGUAGE_ID_BY_LOCALE['az'])
                    ->where('su.store_id', '=', self::SOURCE_STORE_ID);
            })
            ->where('p.manufacturer_id', $manufacturerId)
            ->select([
                'p.product_id',
                'p.sku',
                'p.model',
                'pd.name',
                'su.keyword as slug',
            ])
            ->orderBy('p.sort_order')
            ->orderBy('p.product_id')
            ->get();

        $matches = [];

        foreach ($sourceProducts as $sourceProduct) {
            $match = $this->resolveLocalProductMatch([
                'sku' => $this->nullIfEmpty($sourceProduct->sku),
                'model' => $this->nullIfEmpty($sourceProduct->model),
                'name' => $this->nullIfEmpty($sourceProduct->name),
                'slug' => $this->nullIfEmpty($sourceProduct->slug),
            ]);

            if ($match !== null) {
                $matches[] = $match;
            }
        }

        return $matches;
    }

    private function resolveProductsFromBrandFilterValue(int $manufacturerId): array
    {
        $manufacturer = $this->sourceManufacturer($manufacturerId);

        if ($manufacturer === null) {
            return [];
        }

        $filterIds = $this->brandFilterIds();

        if ($filterIds === []) {
            return [];
        }

        $brandValueIds = $this->brandValueIds($filterIds, $manufacturer);

        if ($brandValueIds === []) {
            return [];
        }

        return DB::table('product_variation_filter_value as pvfv')
            ->join('product_variations as pv', 'pv.id', '=', 'pvfv.product_variation_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->whereIn('pvfv.product_filter_value_id', $brandValueIds)
            ->select([
                'p.id as product_id',
                'pv.id as product_variation_id',
            ])
            ->orderBy('p.sort_order')
            ->orderBy('p.id')
            ->orderBy('pv.id')
            ->get()
            ->map(fn (object $row): array => [
                'product_id' => (int) $row->product_id,
                'product_variation_id' => (int) $row->product_variation_id,
            ])
            ->values()
            ->all();
    }

    private function resolveProductsFromBrandMenu(int $manufacturerId): array
    {
        $menuIds = [];

        $directMenu = Menu::query()->find($manufacturerId);

        if ($directMenu !== null) {
            $menuIds[] = (int) $directMenu->id;
        }

        $manufacturer = $this->sourceManufacturer($manufacturerId);

        if ($manufacturer !== null) {
            $name = $this->nullIfEmpty($manufacturer->name);
            $slug = $this->resolveManufacturerSlug($manufacturerId, (string) $manufacturer->name, 'az');

            $matchedMenuIds = MenuTranslation::query()
                ->where(function ($query) use ($name, $slug): void {
                    if ($name !== null) {
                        $query->orWhere('name', $name);
                    }

                    if ($slug !== null) {
                        $query->orWhere('link', $slug);
                    }
                })
                ->pluck('menu_id')
                ->map(fn ($value): int => (int) $value)
                ->filter(fn (int $value): bool => $value > 0)
                ->values()
                ->all();

            $menuIds = array_merge($menuIds, $matchedMenuIds);
        }

        $menuIds = collect($menuIds)->unique()->values()->all();

        if ($menuIds === []) {
            return [];
        }

        return DB::table('product_menu as pm')
            ->join('products as p', 'p.id', '=', 'pm.product_id')
            ->join('product_variations as pv', 'pv.product_id', '=', 'p.id')
            ->whereIn('pm.menu_id', $menuIds)
            ->select([
                'p.id as product_id',
                DB::raw('MIN(pv.id) as product_variation_id'),
            ])
            ->groupBy('p.id')
            ->orderBy('p.sort_order')
            ->orderBy('p.id')
            ->get()
            ->map(fn (object $row): array => [
                'product_id' => (int) $row->product_id,
                'product_variation_id' => (int) $row->product_variation_id,
            ])
            ->values()
            ->all();
    }

    private function resolveLocalProductMatch(array $relatedProduct): ?array
    {
        $sku = $this->nullIfEmpty($relatedProduct['sku'] ?? null);
        $model = $this->nullIfEmpty($relatedProduct['model'] ?? null);
        $name = $this->nullIfEmpty($relatedProduct['name'] ?? null);
        $slug = $this->nullIfEmpty($relatedProduct['slug'] ?? null);

        if ($sku !== null) {
            $variation = ProductVariation::query()
                ->where('sku', $sku)
                ->first();

            if ($variation !== null) {
                return [
                    'product_id' => (int) $variation->product_id,
                    'product_variation_id' => (int) $variation->id,
                ];
            }
        }

        if ($model !== null) {
            $variation = ProductVariation::query()
                ->where('model', $model)
                ->first();

            if ($variation !== null) {
                return [
                    'product_id' => (int) $variation->product_id,
                    'product_variation_id' => (int) $variation->id,
                ];
            }
        }

        if ($slug !== null) {
            $translation = ProductVariationTranslation::query()
                ->where('slug', $slug)
                ->with('variation')
                ->first();

            if ($translation !== null && $translation->variation !== null) {
                return [
                    'product_id' => (int) $translation->variation->product_id,
                    'product_variation_id' => (int) $translation->product_variation_id,
                ];
            }
        }

        if ($name !== null) {
            $translation = ProductVariationTranslation::query()
                ->where('name', $name)
                ->with('variation')
                ->first();

            if ($translation !== null && $translation->variation !== null) {
                return [
                    'product_id' => (int) $translation->variation->product_id,
                    'product_variation_id' => (int) $translation->product_variation_id,
                ];
            }

            $translation = ProductVariationTranslation::query()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->with('variation')
                ->first();

            if ($translation !== null && $translation->variation !== null) {
                return [
                    'product_id' => (int) $translation->variation->product_id,
                    'product_variation_id' => (int) $translation->product_variation_id,
                ];
            }
        }

        return null;
    }

    private function brandFilterIds(): array
    {
        return DB::table('product_filter_translations')
            ->where(function ($query): void {
                foreach (self::BRAND_FILTER_KEYWORDS as $keyword) {
                    $query->orWhere('name', 'like', '%' . $keyword . '%')
                        ->orWhere('slug', 'like', '%' . $keyword . '%');
                }
            })
            ->pluck('product_filter_id')
            ->map(fn ($value): int => (int) $value)
            ->unique()
            ->values()
            ->all();
    }

    private function brandValueIds(array $filterIds, object $manufacturer): array
    {
        $manufacturerName = $this->nullIfEmpty($manufacturer->name);
        $manufacturerSlugs = [];

        foreach (array_keys(self::SOURCE_LANGUAGE_ID_BY_LOCALE) as $locale) {
            $manufacturerSlugs[] = $this->resolveManufacturerSlug((int) $manufacturer->manufacturer_id, (string) $manufacturer->name, $locale);
        }

        $manufacturerSlugs = collect($manufacturerSlugs)
            ->filter(fn (?string $slug): bool => $slug !== null && $slug !== '')
            ->unique()
            ->values()
            ->all();

        $valueIds = DB::table('product_filter_values')
            ->whereIn('product_filter_id', $filterIds)
            ->pluck('id')
            ->map(fn ($value): int => (int) $value)
            ->values()
            ->all();

        if ($valueIds === []) {
            return [];
        }

        return DB::table('product_filter_value_translations')
            ->whereIn('product_filter_value_id', $valueIds)
            ->where(function ($query) use ($manufacturerName, $manufacturerSlugs): void {
                if ($manufacturerName !== null) {
                    $query->orWhere('name', $manufacturerName)
                        ->orWhereRaw('LOWER(name) = ?', [mb_strtolower($manufacturerName)]);
                }

                if ($manufacturerSlugs !== []) {
                    $query->orWhereIn('slug', $manufacturerSlugs);
                }
            })
            ->pluck('product_filter_value_id')
            ->map(fn ($value): int => (int) $value)
            ->unique()
            ->values()
            ->all();
    }

    private function sourcePartners(): Collection
    {
        $descriptions = $this->sourcePartnerDescriptions();

        return DB::table('oc_partners')
            ->select([
                'partner_id',
                'name',
                'manufacturer_id',
                'text',
                'image',
                'banner',
                'site',
                'phone',
                'email',
                'facebook',
                'linkedin',
                'instagram',
                'status',
                'sort_order',
            ])
            ->orderBy('sort_order')
            ->orderBy('partner_id')
            ->get()
            ->map(function (object $partner) use ($descriptions): array {
                return [
                    'partner_id' => (int) $partner->partner_id,
                    'base_name' => $this->nullIfEmpty($partner->name),
                    'manufacturer_id' => (int) $partner->manufacturer_id,
                    'base_text' => $this->nullIfEmpty($partner->text),
                    'image' => $this->nullIfEmpty($partner->image),
                    'banner' => $this->nullIfEmpty($partner->banner),
                    'site' => $this->nullIfEmpty($partner->site),
                    'phone' => $this->nullIfEmpty($partner->phone),
                    'email' => $this->nullIfEmpty($partner->email),
                    'facebook' => $this->nullIfEmpty($partner->facebook),
                    'linkedin' => $this->nullIfEmpty($partner->linkedin),
                    'instagram' => $this->nullIfEmpty($partner->instagram),
                    'status' => (bool) $partner->status,
                    'sort_order' => (int) $partner->sort_order,
                    'descriptions' => $descriptions[(int) $partner->partner_id] ?? [],
                ];
            });
    }

    private function sourcePartnerDescriptions(): array
    {
        $descriptions = [];

        $rows = DB::table('oc_partner_description')
            ->whereIn('language_id', array_values(self::SOURCE_LANGUAGE_ID_BY_LOCALE))
            ->select([
                'partner_id',
                'language_id',
                'name',
                'text',
                'meta_title',
                'meta_description',
                'meta_keyword',
            ])
            ->orderBy('partner_id')
            ->orderBy('language_id')
            ->get();

        $localeBySourceLanguageId = array_flip(self::SOURCE_LANGUAGE_ID_BY_LOCALE);

        foreach ($rows as $row) {
            $partnerId = (int) $row->partner_id;
            $sourceLanguageId = (int) $row->language_id;
            $locale = $localeBySourceLanguageId[$sourceLanguageId] ?? null;

            if ($locale === null) {
                continue;
            }

            $descriptions[$partnerId][$locale] = [
                'name' => $this->nullIfEmpty($row->name) ?? '',
                'text' => $this->nullIfEmpty($row->text) ?? '',
                'meta_title' => $this->nullIfEmpty($row->meta_title) ?? '',
                'meta_description' => $this->nullIfEmpty($row->meta_description) ?? '',
                'meta_keyword' => $this->nullIfEmpty($row->meta_keyword) ?? '',
            ];
        }

        return $descriptions;
    }

    private function sourceManufacturer(int $manufacturerId): ?object
    {
        return DB::table('oc_manufacturer')
            ->where('manufacturer_id', $manufacturerId)
            ->select([
                'manufacturer_id',
                'name',
            ])
            ->first();
    }

    private function resolveManufacturerSlug(int $manufacturerId, string $name, string $locale): string
    {
        $keyword = $this->findSeoKeyword(['manufacturer_id=' . $manufacturerId], self::SOURCE_LANGUAGE_ID_BY_LOCALE[$locale]);

        if ($keyword !== null) {
            return $keyword;
        }

        $slug = Str::slug($name);

        if ($slug !== '') {
            return $slug;
        }

        return 'brand-' . $manufacturerId . '-' . $locale;
    }

    private function resolvePartnerSlug(int $partnerId, string $name, string $locale, int $sourceLanguageId): string
    {
        $queries = [
            'partner_id=' . $partnerId,
            'brand_news_id=' . $partnerId,
            'brand_news=' . $partnerId,
        ];

        $keyword = $this->findSeoKeyword($queries, $sourceLanguageId);

        if ($keyword !== null) {
            return $keyword;
        }

        $slug = Str::slug($name);

        if ($slug !== '') {
            return $slug;
        }

        return 'brand-news-' . $partnerId . '-' . $locale;
    }

    private function findSeoKeyword(array $queries, int $sourceLanguageId): ?string
    {
        $row = DB::table('oc_seo_url')
            ->where('store_id', self::SOURCE_STORE_ID)
            ->where('language_id', $sourceLanguageId)
            ->whereIn('query', $queries)
            ->select(['keyword'])
            ->first();

        if ($row === null || ! is_string($row->keyword)) {
            return null;
        }

        $keyword = trim($row->keyword);

        return $keyword === '' ? null : $keyword;
    }

    private function replaceRemoteImagesInHtml(string $html, string $directory): string
    {
        if ($html === '') {
            return '';
        }

        if (! preg_match_all('/<img\b[^>]*\bsrc=(["\'])(.*?)\1[^>]*>/i', $html, $matches)) {
            return $html;
        }

        $replacements = [];

        foreach ($matches[2] as $sourceUrl) {
            $sourceUrl = trim((string) html_entity_decode($sourceUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            if ($sourceUrl === '' || isset($replacements[$sourceUrl])) {
                continue;
            }

            $uploadedPath = $this->uploadSourceAsset($sourceUrl, $directory);

            if ($uploadedPath === null) {
                continue;
            }

            $replacements[$sourceUrl] = Storage::disk('public')->url($uploadedPath);
        }

        foreach ($replacements as $sourceUrl => $uploadedUrl) {
            $html = str_replace($sourceUrl, $uploadedUrl, $html);
            $html = str_replace(e($sourceUrl), $uploadedUrl, $html);
        }

        return $html;
    }

    private function uploadSourceAsset(?string $source, string $directory): ?string
    {
        $source = $this->nullIfEmpty($source);

        if ($source === null) {
            return null;
        }

        if ($this->isRemoteUrl($source)) {
            return $this->uploadRemoteAsset($source, $directory);
        }

        return $this->uploadLocalAsset($source, $directory);
    }

    private function uploadLocalAsset(string $relativePath, string $directory): ?string
    {
        $fullPath = $this->sourceAbsolutePath($relativePath);

        if (! File::exists($fullPath) || ! File::isFile($fullPath)) {
            return null;
        }

        return $this->storeUploadedFileFromPath($fullPath, $directory);
    }

    private function uploadRemoteAsset(string $url, string $directory): ?string
    {
        $normalizedUrl = $this->normalizeRemoteUrl($url);

        if ($normalizedUrl === null) {
            return null;
        }

        $cacheKey = md5($normalizedUrl . '|' . $directory);

        if (array_key_exists($cacheKey, $this->uploadedRemoteAssets)) {
            return $this->uploadedRemoteAssets[$cacheKey];
        }

        try {
            $response = Http::timeout(self::HTTP_TIMEOUT_SECONDS)
                ->connectTimeout(self::HTTP_TIMEOUT_SECONDS)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 BrandNewsTransferBot',
                ])
                ->get($normalizedUrl);

            if (! $response->successful()) {
                $this->uploadedRemoteAssets[$cacheKey] = null;

                return null;
            }

            $content = $response->body();

            if ($content === '') {
                $this->uploadedRemoteAssets[$cacheKey] = null;

                return null;
            }

            $extension = $this->resolveRemoteExtension($normalizedUrl, $response->header('Content-Type'));
            $temporaryPath = tempnam(sys_get_temp_dir(), 'brand_news_asset_');

            if ($temporaryPath === false) {
                $this->uploadedRemoteAssets[$cacheKey] = null;

                return null;
            }

            $temporaryPathWithExtension = $temporaryPath . '.' . $extension;
            File::put($temporaryPathWithExtension, $content);
            File::delete($temporaryPath);

            $storedPath = $this->storeUploadedFileFromPath($temporaryPathWithExtension, $directory);

            File::delete($temporaryPathWithExtension);

            $this->uploadedRemoteAssets[$cacheKey] = $storedPath;

            return $storedPath;
        } catch (\Throwable) {
            $this->uploadedRemoteAssets[$cacheKey] = null;

            return null;
        }
    }

    private function storeUploadedFileFromPath(string $path, string $directory): ?string
    {
        if (! File::exists($path) || ! File::isFile($path)) {
            return null;
        }

        $uploadedFile = new UploadedFile(
            $path,
            basename($path),
            File::mimeType($path) ?: 'application/octet-stream',
            null,
            true
        );

        return $this->fileUploadService->storeRaw($uploadedFile, $directory);
    }

    private function sourceAssetExists(?string $source): bool
    {
        $source = $this->nullIfEmpty($source);

        if ($source === null) {
            return false;
        }

        if ($this->isRemoteUrl($source)) {
            return true;
        }

        return File::exists($this->sourceAbsolutePath($source));
    }

    private function isRemoteUrl(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false
            && in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true);
    }

    private function normalizeRemoteUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, '//')) {
            $url = 'https:' . $url;
        }

        if (! $this->isRemoteUrl($url)) {
            return null;
        }

        return $url;
    }

    private function resolveRemoteExtension(string $url, ?string $contentType): string
    {
        $pathExtension = strtolower(pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

        if (in_array($pathExtension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true)) {
            return $pathExtension;
        }

        $contentType = strtolower(trim((string) $contentType));

        return match (true) {
            str_contains($contentType, 'jpeg') => 'jpg',
            str_contains($contentType, 'png') => 'png',
            str_contains($contentType, 'webp') => 'webp',
            str_contains($contentType, 'gif') => 'gif',
            str_contains($contentType, 'svg') => 'svg',
            default => 'jpg',
        };
    }

    private function sourceAbsolutePath(string $relativePath): string
    {
        return public_path('uploads/opencart/' . ltrim($relativePath, '/'));
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
