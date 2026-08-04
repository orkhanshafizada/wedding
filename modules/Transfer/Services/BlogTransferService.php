<?php

namespace Modules\Transfer\Services;

use App\Models\Language;
use App\Services\Upload\FileUploadService;
use App\Services\Upload\ImageUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
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
use Modules\Transfer\Jobs\ProcessBlogTransferChunkJob;
use Modules\Transfer\Jobs\ProcessBlogTransferJob;
use RuntimeException;
use Throwable;

class BlogTransferService
{
    private const SOURCE_LANGUAGE_IDS = [
        'az' => 3,
        'en' => 8,
        'ru' => 9,
    ];

    private const TARGET_LOCALES = [
        'az',
        'en',
        'ru',
    ];

    private const SOURCE_STORE_ID = 0;

    private const ROOT_CATEGORY_ID = 1;

    private const FOOTER_PARENT_NAME = 'Tvim';

    private const HTTP_TIMEOUT_SECONDS = 20;

    private const DEFAULT_CHUNK_SIZE = 20;

    private array $uploadedRemoteAssets = [];

    private ?Collection $targetLanguages = null;

    public function __construct(
        private readonly ImageUploadService $imageUploadService,
        private readonly FileUploadService $fileUploadService
    ) {
    }

    public function preview(): array
    {
        $this->assertSourceLanguages();
        $this->targetLanguages();

        $categories = $this->sourceCategories();
        $previewStoryIds = $this->sourceStoryIds()
            ->take(20)
            ->values()
            ->all();

        $stories = $this->sourceStories($previewStoryIds);

        return [
            'category_count' => $categories->count(),
            'story_count' => $this->sourceStoryIds()->count(),
            'language_mappings' => $this->languageMappings(),
            'categories' => $categories
                ->map(function (array $category): array {
                    return [
                        'category_id' => $category['category_id'],
                        'parent_id' => $category['parent_id'],
                        'name' => $this->translationValue(
                            $category['translations'],
                            'az',
                            'name'
                        ),
                        'names' => collect(self::TARGET_LOCALES)
                            ->mapWithKeys(fn (string $locale): array => [
                                $locale => $this->translationValue(
                                    $category['translations'],
                                    $locale,
                                    'name'
                                ),
                            ])
                            ->all(),
                        'keywords' => collect(self::TARGET_LOCALES)
                            ->mapWithKeys(fn (string $locale): array => [
                                $locale => $this->resolveCategorySlug(
                                    categoryId: $category['category_id'],
                                    locale: $locale,
                                    name: $this->translationValue(
                                        $category['translations'],
                                        $locale,
                                        'name'
                                    )
                                ),
                            ])
                            ->all(),
                        'status' => $category['status'],
                        'sort_order' => $category['sort_order'],
                    ];
                })
                ->values()
                ->all(),
            'stories' => $stories
                ->map(function (array $story): array {
                    return [
                        'news_id' => $story['news_id'],
                        'name' => $this->translationValue(
                            $story['translations'],
                            'az',
                            'name'
                        ),
                        'names' => collect(self::TARGET_LOCALES)
                            ->mapWithKeys(fn (string $locale): array => [
                                $locale => $this->translationValue(
                                    $story['translations'],
                                    $locale,
                                    'name'
                                ),
                            ])
                            ->all(),
                        'keywords' => collect(self::TARGET_LOCALES)
                            ->mapWithKeys(fn (string $locale): array => [
                                $locale => $this->resolveStorySlug(
                                    newsId: $story['news_id'],
                                    locale: $locale,
                                    name: $this->translationValue(
                                        $story['translations'],
                                        $locale,
                                        'name'
                                    )
                                ),
                            ])
                            ->all(),
                        'category_ids' => $story['category_ids'],
                        'related_products_count' => count($story['related_products']),
                        'status' => $story['status'],
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    public function dispatchImport(): array
    {
        $this->assertSourceLanguages();
        $this->targetLanguages();

        $storyCount = $this->sourceStoryIds()->count();

        if ($storyCount === 0) {
            throw new RuntimeException('No active OpenCart blog records were found.');
        }

        ProcessBlogTransferJob::dispatch()
            ->onQueue('transfers');

        return [
            'total_stories' => $storyCount,
            'jobs_dispatched' => 1,
            'queue' => 'transfers',
        ];
    }

    public function import(): array
    {
        return $this->processImport();
    }

    public function processImport(int $chunkSize = self::DEFAULT_CHUNK_SIZE): array
    {
        $this->assertSourceLanguages();
        $this->targetLanguages();

        $chunkSize = max(1, $chunkSize);

        $categories = $this->sourceCategories();
        $storyIds = $this->sourceStoryIds();

        if ($storyIds->isEmpty()) {
            throw new RuntimeException('No active OpenCart blog records were found.');
        }

        $menuSetup = DB::transaction(function () use ($categories): array {
            return $this->prepareMenus($categories);
        });

        $jobs = $storyIds
            ->chunk($chunkSize)
            ->map(fn (Collection $chunk): ProcessBlogTransferChunkJob => new ProcessBlogTransferChunkJob(
                storyIds: $chunk->values()->all(),
                categoryMenuMap: $menuSetup['category_menu_map'],
                rootBlogMenuId: $menuSetup['root_blog_menu_id']
            ))
            ->values()
            ->all();

        $batch = Bus::batch($jobs)
            ->name('blog-transfer-' . now()->format('Y-m-d-His'))
            ->allowFailures()
            ->onQueue('transfers')
            ->dispatch();

        return [
            'batch_id' => $batch->id,
            'story_count' => $storyIds->count(),
            'chunk_count' => count($jobs),
            'menus' => $menuSetup['menu_count'],
        ];
    }

    public function importChunk(
        array $storyIds,
        array $categoryMenuMap,
        int $rootBlogMenuId
    ): array {
        $stories = $this->sourceStories($storyIds);

        $importedCount = 0;
        $skippedCount = 0;

        foreach ($stories as $story) {
            try {
                DB::transaction(function () use (
                    $story,
                    $categoryMenuMap,
                    $rootBlogMenuId
                ): void {
                    $targetMenuId = $this->resolveTargetMenuId(
                        categoryIds: $story['category_ids'],
                        categoryMenuMap: $categoryMenuMap,
                        fallbackMenuId: $rootBlogMenuId
                    );

                    $this->resolveOrCreateGrid(
                        story: $story,
                        menuId: $targetMenuId
                    );
                });

                $importedCount++;
            } catch (Throwable $throwable) {
                $skippedCount++;

                report(new RuntimeException(
                    'OpenCart blog transfer failed for news ID '
                    . $story['news_id']
                    . '.',
                    0,
                    $throwable
                ));
            }
        }

        return [
            'imported_count' => $importedCount,
            'skipped_count' => $skippedCount,
        ];
    }

    private function prepareMenus(Collection $categories): array
    {
        $menuCount = 0;

        $footerParentMenu = $this->resolveOrCreateFooterParentMenu();
        $menuCount++;

        $rootCategory = $categories->firstWhere(
            'category_id',
            self::ROOT_CATEGORY_ID
        );

        if ($rootCategory === null) {
            throw new RuntimeException('OpenCart root blog category was not found.');
        }

        $rootBlogMenu = $this->resolveOrCreateRootBlogMenu(
            footerParentMenu: $footerParentMenu,
            category: $rootCategory
        );

        $menuCount++;

        $categoryMenuMap = [
            self::ROOT_CATEGORY_ID => (int) $rootBlogMenu->id,
        ];

        foreach (
            $categories->where(
                'category_id',
                '!=',
                self::ROOT_CATEGORY_ID
            ) as $category
        ) {
            $menu = $this->resolveOrCreateChildBlogMenu(
                rootBlogMenu: $rootBlogMenu,
                category: $category
            );

            $categoryMenuMap[$category['category_id']] = (int) $menu->id;
            $menuCount++;
        }

        return [
            'menu_count' => $menuCount,
            'root_blog_menu_id' => (int) $rootBlogMenu->id,
            'category_menu_map' => $categoryMenuMap,
        ];
    }

    private function resolveOrCreateFooterParentMenu(): Menu
    {
        $translation = MenuTranslation::query()
            ->whereIn('locale', self::TARGET_LOCALES)
            ->where('name', self::FOOTER_PARENT_NAME)
            ->where('link', '#')
            ->whereHas('menu', function ($query): void {
                $query
                    ->whereNull('parent_id')
                    ->where('type', 'link');
            })
            ->first();

        $menu = $translation?->menu ?? new Menu();

        $menu->fill([
            'parent_id' => null,
            'type' => 'link',
            'view_type' => 'default',
            'status' => true,
            'show_on_main_page' => false,
            'in_header' => false,
            'in_footer' => true,
            'icon' => null,
            'icon_image' => null,
            'text_color' => null,
            'bg_color' => null,
            'sort_order' => 0,
        ]);

        $menu->save();

        foreach (self::TARGET_LOCALES as $locale) {
            MenuTranslation::query()->updateOrCreate(
                [
                    'menu_id' => $menu->id,
                    'locale' => $locale,
                ],
                [
                    'name' => self::FOOTER_PARENT_NAME,
                    'link' => '#',
                    'meta_title' => null,
                    'meta_description' => null,
                    'meta_keywords' => null,
                ]
            );
        }

        return $menu;
    }

    private function resolveOrCreateRootBlogMenu(
        Menu $footerParentMenu,
        array $category
    ): Menu {
        $menu = $this->resolveExistingBlogMenu(
            translations: $category['translations'],
            parentId: (int) $footerParentMenu->id,
            categoryId: $category['category_id']
        ) ?? new Menu();

        $menu->fill([
            'parent_id' => $footerParentMenu->id,
            'type' => 'grids',
            'view_type' => 'blog',
            'status' => $category['status'],
            'show_on_main_page' => false,
            'in_header' => false,
            'in_footer' => true,
            'icon' => null,
            'icon_image' => $this->uploadMenuIcon(
                $category['image'],
                'menus/blog'
            ),
            'text_color' => null,
            'bg_color' => null,
            'sort_order' => $category['sort_order'],
        ]);

        $menu->save();

        $this->syncBlogMenuTranslations(
            menu: $menu,
            category: $category
        );

        return $menu;
    }

    private function resolveOrCreateChildBlogMenu(
        Menu $rootBlogMenu,
        array $category
    ): Menu {
        $menu = $this->resolveExistingBlogMenu(
            translations: $category['translations'],
            parentId: (int) $rootBlogMenu->id,
            categoryId: $category['category_id']
        ) ?? new Menu();

        $menu->fill([
            'parent_id' => $rootBlogMenu->id,
            'type' => 'grids',
            'view_type' => 'blog',
            'status' => $category['status'],
            'show_on_main_page' => false,
            'in_header' => false,
            'in_footer' => false,
            'icon' => null,
            'icon_image' => $this->uploadMenuIcon(
                $category['image'],
                'menus/blog'
            ),
            'text_color' => null,
            'bg_color' => null,
            'sort_order' => $category['sort_order'],
        ]);

        $menu->save();

        $this->syncBlogMenuTranslations(
            menu: $menu,
            category: $category
        );

        return $menu;
    }

    private function syncBlogMenuTranslations(
        Menu $menu,
        array $category
    ): void {
        foreach (self::TARGET_LOCALES as $locale) {
            $translation = $this->requiredTranslation(
                translations: $category['translations'],
                locale: $locale,
                recordDescription: 'blog category '
                . $category['category_id']
            );

            MenuTranslation::query()->updateOrCreate(
                [
                    'menu_id' => $menu->id,
                    'locale' => $locale,
                ],
                [
                    'name' => $translation['name'],
                    'link' => $this->resolveCategorySlug(
                        categoryId: $category['category_id'],
                        locale: $locale,
                        name: $translation['name']
                    ),
                    'meta_title' => $this->nullIfEmpty(
                        $translation['meta_title']
                    ),
                    'meta_description' => $this->nullIfEmpty(
                        $translation['meta_description']
                    ),
                    'meta_keywords' => $this->nullIfEmpty(
                        $translation['meta_keyword']
                    ),
                ]
            );
        }
    }

    private function resolveExistingBlogMenu(
        array $translations,
        int $parentId,
        int $categoryId
    ): ?Menu {
        foreach (self::TARGET_LOCALES as $locale) {
            $name = $this->translationValue(
                $translations,
                $locale,
                'name'
            );

            if ($name === '') {
                continue;
            }

            $slug = $this->resolveCategorySlug(
                categoryId: $categoryId,
                locale: $locale,
                name: $name
            );

            $translation = MenuTranslation::query()
                ->where('locale', $locale)
                ->where('link', $slug)
                ->whereHas('menu', function ($query) use ($parentId): void {
                    $query
                        ->where('parent_id', $parentId)
                        ->where('type', 'grids')
                        ->where('view_type', 'blog');
                })
                ->first();

            if ($translation !== null) {
                return $translation->menu;
            }
        }

        foreach (self::TARGET_LOCALES as $locale) {
            $name = $this->translationValue(
                $translations,
                $locale,
                'name'
            );

            if ($name === '') {
                continue;
            }

            $translation = MenuTranslation::query()
                ->where('locale', $locale)
                ->where('name', $name)
                ->whereHas('menu', function ($query) use ($parentId): void {
                    $query
                        ->where('parent_id', $parentId)
                        ->where('type', 'grids')
                        ->where('view_type', 'blog');
                })
                ->first();

            if ($translation !== null) {
                return $translation->menu;
            }
        }

        return null;
    }

    private function resolveTargetMenuId(
        array $categoryIds,
        array $categoryMenuMap,
        int $fallbackMenuId
    ): int {
        foreach ($categoryIds as $categoryId) {
            $categoryId = (int) $categoryId;

            if (
                $categoryId !== self::ROOT_CATEGORY_ID
                && isset($categoryMenuMap[$categoryId])
            ) {
                return (int) $categoryMenuMap[$categoryId];
            }
        }

        return $fallbackMenuId;
    }

    private function resolveOrCreateGrid(
        array $story,
        int $menuId
    ): void {
        $names = [];
        $slugs = [];
        $contents = [];
        $locations = [];
        $metaTitles = [];
        $metaDescriptions = [];
        $metaKeywords = [];

        foreach (self::TARGET_LOCALES as $locale) {
            $translation = $this->requiredTranslation(
                translations: $story['translations'],
                locale: $locale,
                recordDescription: 'blog story ' . $story['news_id']
            );

            $name = $translation['name'];

            $slug = $this->resolveStorySlug(
                newsId: $story['news_id'],
                locale: $locale,
                name: $name
            );

            $names[$locale] = $name;
            $slugs[$locale] = $slug;

            $contents[$locale] = $this->replaceRemoteImagesInHtml(
                html: $this->decodeHtml($translation['description']),
                directory: 'grids/blog/'
                . $story['news_id']
                . '/content/'
                . $locale
            );

            $locations[$locale] = $this->nullIfEmpty(
                $translation['primary_category_name']
            ) ?? '';

            $metaTitles[$locale] = $this->nullIfEmpty(
                $translation['meta_title']
            ) ?? $name;

            $metaDescriptions[$locale] = $this->nullIfEmpty(
                $translation['meta_description']
            ) ?? '';

            $metaKeywords[$locale] = $this->nullIfEmpty(
                $translation['meta_keyword']
            ) ?? '';
        }

        $grid = Grid::query()
            ->where('menu_id', $menuId)
            ->where(function ($query) use ($slugs): void {
                foreach ($slugs as $locale => $slug) {
                    $query->orWhereRaw(
                        "JSON_UNQUOTE(JSON_EXTRACT(slug, '$.\"{$locale}\"')) = ?",
                        [$slug]
                    );
                }
            })
            ->first();

        if ($grid === null) {
            $grid = new Grid();
        }

        $bannerPath = $this->resolveGridBanner($story);

        $grid->fill([
            'menu_id' => $menuId,
            'datetime1' => $story['date_added'],
            'datetime2' => null,
            'banner' => $bannerPath,
            'name' => $names,
            'slug' => $slugs,
            'content' => $contents,
            'location_or_group' => $locations,
            'meta_title' => $metaTitles,
            'meta_description' => $metaDescriptions,
            'meta_keywords' => $metaKeywords,
            'is_active' => $story['status'],
            'sort_order' => 0,
        ]);

        $grid->save();

        $this->syncGridMedia(
            grid: $grid,
            story: $story,
            bannerPath: $bannerPath
        );

        $this->syncRelatedProducts(
            grid: $grid,
            relatedProducts: $story['related_products']
        );
    }

    private function syncGridMedia(
        Grid $grid,
        array $story,
        ?string $bannerPath
    ): void {
        $existingMedia = GridMedia::query()
            ->where('grid_id', $grid->id)
            ->get();

        foreach ($existingMedia as $media) {
            $path = (string) $media->path;

            if (
                $path !== ''
                && Storage::disk('public')->exists($path)
            ) {
                Storage::disk('public')->delete($path);
            }

            $media->delete();
        }

        $galleryMain = $this->resolveGalleryMainPhoto(
            story: $story,
            bannerPath: $bannerPath,
            grid: $grid
        );

        if ($galleryMain === null) {
            return;
        }

        GridMedia::query()->create([
            'grid_id' => $grid->id,
            'type' => 'image',
            'path' => $galleryMain,
            'original_name' => basename($galleryMain),
            'is_main' => true,
            'sort_order' => 0,
        ]);
    }

    private function syncRelatedProducts(
        Grid $grid,
        array $relatedProducts
    ): void {
        DB::table('grids_related_products')
            ->where('grid_id', $grid->id)
            ->delete();

        if ($relatedProducts === []) {
            return;
        }

        $rows = [];
        $usedProductIds = [];
        $usedVariationIds = [];
        $sortOrder = 0;
        $timestamp = now();

        foreach ($relatedProducts as $relatedProduct) {
            $match = $this->resolveLocalProductMatch($relatedProduct);

            if ($match === null) {
                continue;
            }

            if (
                isset($usedProductIds[$match['product_id']])
                || isset(
                    $usedVariationIds[
                    $match['product_variation_id']
                    ]
                )
            ) {
                continue;
            }

            $usedProductIds[$match['product_id']] = true;
            $usedVariationIds[$match['product_variation_id']] = true;

            $rows[] = [
                'grid_id' => $grid->id,
                'product_id' => $match['product_id'],
                'product_variation_id' => $match['product_variation_id'],
                'sort_order' => $sortOrder++,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        if ($rows !== []) {
            DB::table('grids_related_products')->insert($rows);
        }
    }

    private function resolveLocalProductMatch(
        array $relatedProduct
    ): ?array {
        $sku = $this->nullIfEmpty(
            $relatedProduct['sku'] ?? null
        );

        $model = $this->nullIfEmpty(
            $relatedProduct['model'] ?? null
        );

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

        foreach (self::TARGET_LOCALES as $locale) {
            $translation = $relatedProduct['translations'][$locale]
                ?? null;

            if (! is_array($translation)) {
                continue;
            }

            $targetLanguageId = $this->targetLanguageId($locale);
            $slug = $this->nullIfEmpty(
                $translation['slug'] ?? null
            );

            $name = $this->nullIfEmpty(
                $translation['name'] ?? null
            );

            if ($slug !== null) {
                $productTranslation = ProductVariationTranslation::query()
                    ->where('language_id', $targetLanguageId)
                    ->where('slug', $slug)
                    ->first();

                if (
                    $productTranslation !== null
                    && $productTranslation->variation !== null
                ) {
                    return [
                        'product_id' => (int) $productTranslation
                            ->variation
                            ->product_id,
                        'product_variation_id' => (int) $productTranslation
                            ->product_variation_id,
                    ];
                }
            }

            if ($name === null) {
                continue;
            }

            $productTranslation = ProductVariationTranslation::query()
                ->where('language_id', $targetLanguageId)
                ->where('name', $name)
                ->first();

            if (
                $productTranslation !== null
                && $productTranslation->variation !== null
            ) {
                return [
                    'product_id' => (int) $productTranslation
                        ->variation
                        ->product_id,
                    'product_variation_id' => (int) $productTranslation
                        ->product_variation_id,
                ];
            }

            $productTranslation = ProductVariationTranslation::query()
                ->where('language_id', $targetLanguageId)
                ->whereRaw(
                    'LOWER(name) = ?',
                    [mb_strtolower($name)]
                )
                ->first();

            if (
                $productTranslation !== null
                && $productTranslation->variation !== null
            ) {
                return [
                    'product_id' => (int) $productTranslation
                        ->variation
                        ->product_id,
                    'product_variation_id' => (int) $productTranslation
                        ->product_variation_id,
                ];
            }
        }

        return null;
    }

    private function resolveGridBanner(array $story): ?string
    {
        $directory = 'grids/blog/'
            . $story['news_id']
            . '/banner';

        return $this->uploadGridImage(
            $story['image_inner'],
            $directory
        ) ?? $this->uploadGridImage(
            $story['image'],
            $directory
        );
    }

    private function resolveGalleryMainPhoto(
        array $story,
        ?string $bannerPath,
        Grid $grid
    ): ?string {
        $directory = 'grids/'
            . $grid->menu_id
            . '/'
            . $grid->id
            . '/gallery';

        $mainPhoto = $this->uploadGridImage(
            $story['image'],
            $directory
        );

        if ($mainPhoto !== null) {
            return $mainPhoto;
        }

        $fallbackInner = $this->uploadGridImage(
            $story['image_inner'],
            $directory
        );

        if (
            $fallbackInner !== null
            && $fallbackInner !== $bannerPath
        ) {
            return $fallbackInner;
        }

        return null;
    }

    private function uploadMenuIcon(
        ?string $relativePath,
        string $directory
    ): ?string {
        return $this->uploadSourceAsset(
            $relativePath,
            $directory
        );
    }

    private function uploadGridImage(
        ?string $relativePath,
        string $directory
    ): ?string {
        return $this->uploadSourceAsset(
            $relativePath,
            $directory
        );
    }

    private function uploadSourceAsset(
        ?string $source,
        string $directory
    ): ?string {
        $source = $this->nullIfEmpty($source);

        if ($source === null) {
            return null;
        }

        $remoteUrl = $this->normalizeRemoteUrl($source);

        if ($remoteUrl !== null) {
            return $this->uploadRemoteAsset(
                $remoteUrl,
                $directory
            );
        }

        return $this->uploadLocalAsset(
            $source,
            $directory
        );
    }

    private function uploadLocalAsset(
        string $relativePath,
        string $directory
    ): ?string {
        $fullPath = $this->sourceAbsolutePath($relativePath);

        if (
            ! File::exists($fullPath)
            || ! File::isFile($fullPath)
        ) {
            return null;
        }

        return $this->storeUploadedFileFromPath(
            $fullPath,
            $directory
        );
    }

    private function uploadRemoteAsset(
        string $url,
        string $directory
    ): ?string {
        $normalizedUrl = $this->normalizeRemoteUrl($url);

        if ($normalizedUrl === null) {
            return null;
        }

        $cacheKey = hash(
            'sha256',
            $normalizedUrl . '|' . $directory
        );

        if (
            array_key_exists(
                $cacheKey,
                $this->uploadedRemoteAssets
            )
        ) {
            return $this->uploadedRemoteAssets[$cacheKey];
        }

        $temporaryPath = null;
        $temporaryPathWithExtension = null;

        try {
            $response = Http::timeout(
                self::HTTP_TIMEOUT_SECONDS
            )
                ->connectTimeout(self::HTTP_TIMEOUT_SECONDS)
                ->retry(2, 500)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 BlogTransferBot',
                ])
                ->get($normalizedUrl);

            if (! $response->successful()) {
                return $this->rememberRemoteAsset(
                    $cacheKey,
                    null
                );
            }

            $content = $response->body();

            if ($content === '') {
                return $this->rememberRemoteAsset(
                    $cacheKey,
                    null
                );
            }

            $extension = $this->resolveRemoteExtension(
                url: $normalizedUrl,
                contentType: $response->header('Content-Type')
            );

            $temporaryPath = tempnam(
                sys_get_temp_dir(),
                'blog_asset_'
            );

            if ($temporaryPath === false) {
                return $this->rememberRemoteAsset(
                    $cacheKey,
                    null
                );
            }

            $temporaryPathWithExtension = $temporaryPath
                . '.'
                . $extension;

            File::put(
                $temporaryPathWithExtension,
                $content
            );

            $storedPath = $this->storeUploadedFileFromPath(
                $temporaryPathWithExtension,
                $directory
            );

            return $this->rememberRemoteAsset(
                $cacheKey,
                $storedPath
            );
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->rememberRemoteAsset(
                $cacheKey,
                null
            );
        } finally {
            if (
                is_string($temporaryPath)
                && File::exists($temporaryPath)
            ) {
                File::delete($temporaryPath);
            }

            if (
                is_string($temporaryPathWithExtension)
                && File::exists($temporaryPathWithExtension)
            ) {
                File::delete($temporaryPathWithExtension);
            }
        }
    }

    private function rememberRemoteAsset(
        string $cacheKey,
        ?string $storedPath
    ): ?string {
        $this->uploadedRemoteAssets[$cacheKey] = $storedPath;

        return $storedPath;
    }

    private function storeUploadedFileFromPath(
        string $path,
        string $directory
    ): ?string {
        if (
            ! File::exists($path)
            || ! File::isFile($path)
        ) {
            return null;
        }

        $uploadedFile = new UploadedFile(
            $path,
            basename($path),
            File::mimeType($path) ?: 'application/octet-stream',
            null,
            true
        );

        return $this->fileUploadService->storeRaw(
            $uploadedFile,
            $directory
        );
    }

    private function replaceRemoteImagesInHtml(
        string $html,
        string $directory
    ): string {
        if ($html === '') {
            return '';
        }

        if (
            ! preg_match_all(
                '/<img\b[^>]*\bsrc=(["\'])(.*?)\1[^>]*>/i',
                $html,
                $matches
            )
        ) {
            return $html;
        }

        $replacements = [];

        foreach ($matches[2] as $sourceUrl) {
            $sourceUrl = trim(
                (string) html_entity_decode(
                    $sourceUrl,
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                )
            );

            if (
                $sourceUrl === ''
                || isset($replacements[$sourceUrl])
            ) {
                continue;
            }

            $uploadedPath = $this->uploadSourceAsset(
                $sourceUrl,
                $directory
            );

            if ($uploadedPath === null) {
                continue;
            }

            $replacements[$sourceUrl] = Storage::disk('public')
                ->url($uploadedPath);
        }

        foreach (
            $replacements as $sourceUrl => $uploadedUrl
        ) {
            $html = str_replace(
                $sourceUrl,
                $uploadedUrl,
                $html
            );

            $html = str_replace(
                e($sourceUrl),
                $uploadedUrl,
                $html
            );
        }

        return $html;
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

        if (
            filter_var($url, FILTER_VALIDATE_URL) === false
        ) {
            return null;
        }

        $scheme = mb_strtolower(
            (string) parse_url($url, PHP_URL_SCHEME)
        );

        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        return $url;
    }

    private function resolveRemoteExtension(
        string $url,
        ?string $contentType
    ): string {
        $pathExtension = strtolower(
            pathinfo(
                (string) parse_url(
                    $url,
                    PHP_URL_PATH
                ),
                PATHINFO_EXTENSION
            )
        );

        if (
            in_array(
                $pathExtension,
                [
                    'jpg',
                    'jpeg',
                    'png',
                    'webp',
                    'gif',
                    'svg',
                ],
                true
            )
        ) {
            return $pathExtension;
        }

        $contentType = strtolower(
            trim((string) $contentType)
        );

        return match (true) {
            str_contains($contentType, 'jpeg') => 'jpg',
            str_contains($contentType, 'png') => 'png',
            str_contains($contentType, 'webp') => 'webp',
            str_contains($contentType, 'gif') => 'gif',
            str_contains($contentType, 'svg') => 'svg',
            default => 'jpg',
        };
    }

    private function sourceCategories(): Collection
    {
        $rows = DB::table('oc_uni_news_category as c')
            ->join(
                'oc_uni_news_category_description as cd',
                'cd.category_id',
                '=',
                'c.category_id'
            )
            ->join(
                'oc_uni_news_category_to_store as cts',
                'cts.category_id',
                '=',
                'c.category_id'
            )
            ->whereIn(
                'cd.language_id',
                array_values(self::SOURCE_LANGUAGE_IDS)
            )
            ->where('cts.store_id', self::SOURCE_STORE_ID)
            ->where(function ($query): void {
                $query
                    ->where(
                        'c.category_id',
                        self::ROOT_CATEGORY_ID
                    )
                    ->orWhere(
                        'c.parent_id',
                        self::ROOT_CATEGORY_ID
                    );
            })
            ->where('c.status', 1)
            ->select([
                'c.category_id',
                'c.parent_id',
                'c.image',
                'c.sort_order',
                'c.status',
                'cd.language_id',
                'cd.name',
                'cd.description',
                'cd.meta_description',
                'cd.meta_keyword',
                'cd.meta_title',
                'cd.meta_h1',
            ])
            ->orderBy('c.parent_id')
            ->orderBy('c.sort_order')
            ->orderBy('c.category_id')
            ->get();

        return $rows
            ->groupBy('category_id')
            ->map(function (Collection $group): array {
                $first = $group->first();
                $translations = [];

                foreach ($group as $row) {
                    $locale = $this->sourceLocale(
                        (int) $row->language_id
                    );

                    if ($locale === null) {
                        continue;
                    }

                    $translations[$locale] = [
                        'name' => (string) $row->name,
                        'description' => (string) $row->description,
                        'meta_description' => $this->nullIfEmpty(
                            $row->meta_description
                        ),
                        'meta_keyword' => $this->nullIfEmpty(
                            $row->meta_keyword
                        ),
                        'meta_title' => $this->nullIfEmpty(
                            $row->meta_title
                        ),
                        'meta_h1' => $this->nullIfEmpty(
                            $row->meta_h1
                        ),
                    ];
                }

                return [
                    'category_id' => (int) $first->category_id,
                    'parent_id' => (int) $first->parent_id,
                    'image' => $this->nullIfEmpty($first->image),
                    'sort_order' => (int) $first->sort_order,
                    'status' => (bool) $first->status,
                    'translations' => $translations,
                ];
            })
            ->values();
    }

    private function sourceStoryIds(): Collection
    {
        return DB::table('oc_uni_news_story as s')
            ->join(
                'oc_uni_news_story_to_store as sts',
                'sts.news_id',
                '=',
                's.news_id'
            )
            ->where('sts.store_id', self::SOURCE_STORE_ID)
            ->where('s.status', 1)
            ->orderByDesc('s.date_added')
            ->orderByDesc('s.news_id')
            ->pluck('s.news_id')
            ->map(fn (mixed $newsId): int => (int) $newsId)
            ->values();
    }

    private function sourceStories(
        array $storyIds = []
    ): Collection {
        $query = DB::table('oc_uni_news_story as s')
            ->join(
                'oc_uni_news_story_description as sd',
                'sd.news_id',
                '=',
                's.news_id'
            )
            ->join(
                'oc_uni_news_story_to_store as sts',
                'sts.news_id',
                '=',
                's.news_id'
            )
            ->leftJoin(
                'oc_uni_news_story_to_category as stc',
                'stc.news_id',
                '=',
                's.news_id'
            )
            ->leftJoin(
                'oc_uni_news_category_description as cd',
                function ($join): void {
                    $join
                        ->on(
                            'cd.category_id',
                            '=',
                            'stc.category_id'
                        )
                        ->on(
                            'cd.language_id',
                            '=',
                            'sd.language_id'
                        );
                }
            )
            ->whereIn(
                'sd.language_id',
                array_values(self::SOURCE_LANGUAGE_IDS)
            )
            ->where('sts.store_id', self::SOURCE_STORE_ID)
            ->where('s.status', 1);

        if ($storyIds !== []) {
            $query->whereIn('s.news_id', $storyIds);
        }

        $rows = $query
            ->select([
                's.news_id',
                's.image',
                's.image_inner',
                's.date_added',
                's.viewed',
                's.status',
                'sd.language_id as story_language_id',
                'sd.name as story_name',
                'sd.description as story_description',
                'sd.meta_description as story_meta_description',
                'sd.meta_keyword as story_meta_keyword',
                'sd.meta_title as story_meta_title',
                'sd.meta_h1 as story_meta_h1',
                'stc.category_id',
                'cd.name as category_name',
            ])
            ->orderByDesc('s.date_added')
            ->orderByDesc('s.news_id')
            ->get();

        $relatedProducts = $this->sourceRelatedProductsByNewsId(
            $storyIds
        );

        return $rows
            ->groupBy('news_id')
            ->map(function (
                Collection $group
            ) use ($relatedProducts): array {
                $first = $group->first();

                $categoryIds = $group
                    ->pluck('category_id')
                    ->filter(
                        fn (mixed $categoryId): bool => $categoryId !== null
                    )
                    ->map(
                        fn (mixed $categoryId): int => (int) $categoryId
                    )
                    ->unique()
                    ->values()
                    ->all();

                $translations = [];

                foreach (
                    self::SOURCE_LANGUAGE_IDS as $locale => $sourceLanguageId
                ) {
                    $localizedRows = $group
                        ->filter(
                            fn (object $row): bool => (int) $row
                                    ->story_language_id === $sourceLanguageId
                        )
                        ->values();

                    if ($localizedRows->isEmpty()) {
                        continue;
                    }

                    $localizedFirst = $localizedRows->first();

                    $primaryCategoryName = $localizedRows
                        ->pluck('category_name')
                        ->filter(
                            fn (mixed $value): bool => is_string($value)
                                && trim($value) !== ''
                        )
                        ->first();

                    $translations[$locale] = [
                        'name' => (string) $localizedFirst->story_name,
                        'description' => (string) $localizedFirst
                            ->story_description,
                        'meta_description' => $this->nullIfEmpty(
                            $localizedFirst->story_meta_description
                        ),
                        'meta_keyword' => $this->nullIfEmpty(
                            $localizedFirst->story_meta_keyword
                        ),
                        'meta_title' => $this->nullIfEmpty(
                            $localizedFirst->story_meta_title
                        ),
                        'meta_h1' => $this->nullIfEmpty(
                            $localizedFirst->story_meta_h1
                        ),
                        'primary_category_name' => $this->nullIfEmpty(
                            $primaryCategoryName
                        ),
                    ];
                }

                $newsId = (int) $first->news_id;

                return [
                    'news_id' => $newsId,
                    'image' => $this->nullIfEmpty($first->image),
                    'image_inner' => $this->nullIfEmpty(
                        $first->image_inner
                    ),
                    'date_added' => $first->date_added,
                    'viewed' => (int) $first->viewed,
                    'status' => (bool) $first->status,
                    'category_ids' => $categoryIds,
                    'translations' => $translations,
                    'related_products' => $relatedProducts->get(
                        $newsId,
                        []
                    ),
                ];
            })
            ->values();
    }

    private function sourceRelatedProductsByNewsId(
        array $storyIds = []
    ): Collection {
        $query = DB::table(
            'oc_uni_news_product_related as r'
        )
            ->leftJoin(
                'oc_product as p',
                'p.product_id',
                '=',
                'r.product_id'
            )
            ->leftJoin(
                'oc_product_description as pd',
                function ($join): void {
                    $join
                        ->on(
                            'pd.product_id',
                            '=',
                            'r.product_id'
                        )
                        ->whereIn(
                            'pd.language_id',
                            array_values(
                                self::SOURCE_LANGUAGE_IDS
                            )
                        );
                }
            )
            ->leftJoin(
                'oc_seo_url as su',
                function ($join): void {
                    $join
                        ->on(
                            'su.query',
                            '=',
                            DB::raw(
                                "CONCAT('product_id=', r.product_id)"
                            )
                        )
                        ->on(
                            'su.language_id',
                            '=',
                            'pd.language_id'
                        )
                        ->where(
                            'su.store_id',
                            '=',
                            self::SOURCE_STORE_ID
                        );
                }
            );

        if ($storyIds !== []) {
            $query->whereIn('r.news_id', $storyIds);
        }

        $rows = $query
            ->select([
                'r.news_id',
                'r.product_id as source_product_id',
                'p.sku',
                'p.model',
                'pd.language_id',
                'pd.name',
                'su.keyword as slug',
            ])
            ->orderBy('r.news_id')
            ->orderBy('r.product_id')
            ->get();

        return $rows
            ->groupBy('news_id')
            ->map(function (Collection $newsRows): array {
                return $newsRows
                    ->groupBy('source_product_id')
                    ->map(function (Collection $productRows): array {
                        $first = $productRows->first();
                        $translations = [];

                        foreach ($productRows as $row) {
                            if ($row->language_id === null) {
                                continue;
                            }

                            $locale = $this->sourceLocale(
                                (int) $row->language_id
                            );

                            if ($locale === null) {
                                continue;
                            }

                            $translations[$locale] = [
                                'name' => $this->nullIfEmpty(
                                    $row->name
                                ),
                                'slug' => $this->nullIfEmpty(
                                    $row->slug
                                ),
                            ];
                        }

                        return [
                            'source_product_id' => (int) $first
                                ->source_product_id,
                            'sku' => $this->nullIfEmpty($first->sku),
                            'model' => $this->nullIfEmpty($first->model),
                            'translations' => $translations,
                        ];
                    })
                    ->values()
                    ->all();
            });
    }

    private function resolveCategorySlug(
        int $categoryId,
        string $locale,
        string $name
    ): string {
        $keyword = $this->findCategoryKeyword(
            categoryId: $categoryId,
            locale: $locale
        );

        if ($keyword !== null) {
            return $keyword;
        }

        $slug = Str::slug($name);

        return $slug !== ''
            ? $slug
            : 'blog-category-' . $categoryId . '-' . $locale;
    }

    private function resolveStorySlug(
        int $newsId,
        string $locale,
        string $name
    ): string {
        $keyword = $this->findStoryKeyword(
            newsId: $newsId,
            locale: $locale
        );

        if ($keyword !== null) {
            return $keyword;
        }

        $slug = Str::slug($name);

        return $slug !== ''
            ? $slug
            : 'blog-' . $newsId . '-' . $locale;
    }

    private function findCategoryKeyword(
        int $categoryId,
        string $locale
    ): ?string {
        return $this->findSeoKeyword(
            queries: [
                'news_category_id=' . $categoryId,
                'uni_news_category_id=' . $categoryId,
                'category_id=' . $categoryId,
                'news_path=' . $categoryId,
            ],
            sourceLanguageId: $this->sourceLanguageId($locale)
        );
    }

    private function findStoryKeyword(
        int $newsId,
        string $locale
    ): ?string {
        return $this->findSeoKeyword(
            queries: [
                'news_id=' . $newsId,
                'uni_news_story_id=' . $newsId,
                'story_id=' . $newsId,
                'blog_id=' . $newsId,
            ],
            sourceLanguageId: $this->sourceLanguageId($locale)
        );
    }

    private function findSeoKeyword(
        array $queries,
        int $sourceLanguageId
    ): ?string {
        $rows = DB::table('oc_seo_url')
            ->where('language_id', $sourceLanguageId)
            ->where('store_id', self::SOURCE_STORE_ID)
            ->whereIn('query', $queries)
            ->select([
                'query',
                'keyword',
            ])
            ->get()
            ->keyBy('query');

        foreach ($queries as $query) {
            $row = $rows->get($query);

            if (
                $row !== null
                && is_string($row->keyword)
                && trim($row->keyword) !== ''
            ) {
                return trim($row->keyword);
            }
        }

        return null;
    }

    private function targetLanguages(): Collection
    {
        if ($this->targetLanguages !== null) {
            return $this->targetLanguages;
        }

        $languages = Language::query()
            ->active()
            ->whereIn(
                DB::raw(
                    'LOWER(SUBSTRING(code, 1, 2))'
                ),
                self::TARGET_LOCALES
            )
            ->get()
            ->mapWithKeys(function (Language $language): array {
                $locale = mb_substr(
                    mb_strtolower(
                        (string) $language->code
                    ),
                    0,
                    2
                );

                return [
                    $locale => $language,
                ];
            });

        $missingLocales = collect(self::TARGET_LOCALES)
            ->reject(
                fn (string $locale): bool => $languages->has($locale)
            )
            ->values()
            ->all();

        if ($missingLocales !== []) {
            throw new RuntimeException(
                'Required active target languages were not found: '
                . implode(', ', $missingLocales)
                . '.'
            );
        }

        $this->targetLanguages = $languages;

        return $this->targetLanguages;
    }

    private function targetLanguageId(string $locale): int
    {
        $language = $this->targetLanguages()->get($locale);

        if (! $language instanceof Language) {
            throw new RuntimeException(
                'Target language was not found for locale: '
                . $locale
                . '.'
            );
        }

        return (int) $language->id;
    }

    private function assertSourceLanguages(): void
    {
        $sourceLanguages = DB::table('oc_language')
            ->whereIn(
                'language_id',
                array_values(self::SOURCE_LANGUAGE_IDS)
            )
            ->get([
                'language_id',
                'code',
            ])
            ->keyBy('language_id');

        foreach (
            self::SOURCE_LANGUAGE_IDS as $locale => $languageId
        ) {
            $sourceLanguage = $sourceLanguages->get($languageId);

            if ($sourceLanguage === null) {
                throw new RuntimeException(
                    'OpenCart language was not found for locale '
                    . $locale
                    . ' and language ID '
                    . $languageId
                    . '.'
                );
            }

            $sourceLocale = mb_substr(
                mb_strtolower(
                    (string) $sourceLanguage->code
                ),
                0,
                2
            );

            if ($sourceLocale !== $locale) {
                throw new RuntimeException(
                    'OpenCart language mapping is invalid for language ID '
                    . $languageId
                    . '. Expected locale: '
                    . $locale
                    . '. Actual locale: '
                    . $sourceLocale
                    . '.'
                );
            }
        }
    }

    private function languageMappings(): array
    {
        return collect(self::SOURCE_LANGUAGE_IDS)
            ->mapWithKeys(fn (
                int $sourceLanguageId,
                string $locale
            ): array => [
                $locale => [
                    'source_language_id' => $sourceLanguageId,
                    'target_language_id' => $this->targetLanguageId(
                        $locale
                    ),
                ],
            ])
            ->all();
    }

    private function sourceLanguageId(string $locale): int
    {
        if (! isset(self::SOURCE_LANGUAGE_IDS[$locale])) {
            throw new RuntimeException(
                'Unsupported OpenCart locale: '
                . $locale
                . '.'
            );
        }

        return self::SOURCE_LANGUAGE_IDS[$locale];
    }

    private function sourceLocale(
        int $sourceLanguageId
    ): ?string {
        $locale = array_search(
            $sourceLanguageId,
            self::SOURCE_LANGUAGE_IDS,
            true
        );

        return is_string($locale)
            ? $locale
            : null;
    }

    private function requiredTranslation(
        array $translations,
        string $locale,
        string $recordDescription
    ): array {
        $translation = $translations[$locale] ?? null;

        if (! is_array($translation)) {
            throw new RuntimeException(
                'OpenCart '
                . $locale
                . ' translation was not found for '
                . $recordDescription
                . '.'
            );
        }

        $name = $this->nullIfEmpty(
            $translation['name'] ?? null
        );

        if ($name === null) {
            throw new RuntimeException(
                'OpenCart '
                . $locale
                . ' name was not found for '
                . $recordDescription
                . '.'
            );
        }

        $translation['name'] = $name;

        return $translation;
    }

    private function translationValue(
        array $translations,
        string $locale,
        string $field
    ): string {
        $value = $translations[$locale][$field] ?? null;

        return $this->nullIfEmpty($value) ?? '';
    }

    private function sourceAbsolutePath(
        string $relativePath
    ): string {
        return public_path(
            'uploads/opencart/'
            . ltrim($relativePath, '/')
        );
    }

    private function decodeHtml(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return html_entity_decode(
            $value,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === ''
            ? null
            : $value;
    }
}
