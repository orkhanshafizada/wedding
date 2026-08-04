<?php

namespace Modules\Transfer\Services;

use App\Services\Upload\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;
use Modules\Menu\Models\Menu;
use Modules\Menu\Models\MenuTranslation;
use RuntimeException;

class ServiceMenuTransferService
{
    private const SOURCE_TABLE = 'oc_uni_setting';

    private const SOURCE_DATA_COLUMN = 'data';

    private const SOURCE_STORE_ID = 0;

    private const TARGET_VIEW_TYPE = 'Services';

    private const SOURCE_LANGUAGE_ID_BY_LOCALE = [
        'az' => 3,
        'en' => 8,
        'ru' => 9,
    ];

    private array $uploadedAssets = [];

    public function __construct(
        private readonly FileUploadService $fileUploadService
    ) {
    }

    public function preview(): array
    {
        $items = $this->sourceItems();

        return [
            'count' => $items->count(),
            'language_count' => count(self::SOURCE_LANGUAGE_ID_BY_LOCALE),
            'source_table' => self::SOURCE_TABLE,
            'view_type' => self::TARGET_VIEW_TYPE,
            'items' => $items
                ->map(function (array $item): array {
                    return [
                        ...$item,
                        'icon_exists' => $this->sourceIconExists(
                            $item['icon_source']
                        ),
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    public function import(): int
    {
        $preparedItems = $this->sourceItems()
            ->map(function (array $item): array {
                $existingMenu = $this->resolveExistingMenu($item);

                return [
                    'item' => $item,
                    'menu' => $existingMenu,
                    'icon_image' => $this->resolveOrUploadIcon(
                        $item,
                        $existingMenu
                    ),
                ];
            });

        return DB::transaction(function () use ($preparedItems): int {
            $importedCount = 0;

            foreach ($preparedItems as $preparedItem) {
                $item = $preparedItem['item'];
                $menu = $preparedItem['menu'] ?? new Menu();

                $menu->fill([
                    'parent_id' => null,
                    'type' => 'content',
                    'view_type' => self::TARGET_VIEW_TYPE,
                    'status' => true,
                    'show_on_main_page' => true,
                    'show_in_sitemap' => false,
                    'in_header' => false,
                    'in_footer' => false,
                    'main_image' => null,
                    'icon' => null,
                    'icon_image' => $preparedItem['icon_image'],
                    'text_color' => null,
                    'bg_color' => null,
                    'sort_order' => $item['sort_order'],
                ]);

                $menu->save();

                $this->syncMenuTranslations($menu, $item);

                $importedCount++;
            }

            return $importedCount;
        });
    }

    private function sourceItems(): Collection
    {
        $settings = $this->sourceSettings();
        $textBanner = data_get($settings, 'home.text_banner');

        if (! is_array($textBanner)) {
            throw new RuntimeException(
                'The home.text_banner configuration is missing from the resolved OpenCart settings.'
            );
        }

        $items = collect($textBanner)
            ->filter(
                fn (mixed $item, mixed $key): bool => is_numeric($key)
                    && is_array($item)
            )
            ->map(function (array $sourceItem, mixed $key): array {
                $sourceKey = (int) $key;
                $translations = [];

                foreach (
                    self::SOURCE_LANGUAGE_ID_BY_LOCALE
                    as $locale => $sourceLanguageId
                ) {
                    $title = $this->requiredSourceValue(
                        sourceItem: $sourceItem,
                        field: 'text_1',
                        sourceLanguageId: $sourceLanguageId,
                        sourceKey: $sourceKey
                    );

                    $description = $this->requiredSourceValue(
                        sourceItem: $sourceItem,
                        field: 'text_2',
                        sourceLanguageId: $sourceLanguageId,
                        sourceKey: $sourceKey
                    );

                    $sourceLink = $this->requiredSourceValue(
                        sourceItem: $sourceItem,
                        field: 'link',
                        sourceLanguageId: $sourceLanguageId,
                        sourceKey: $sourceKey
                    );

                    $link = $this->normalizeLink($sourceLink);

                    if ($link === null) {
                        throw new RuntimeException(
                            sprintf(
                                'Unable to normalize the service link for source item %d and locale %s.',
                                $sourceKey,
                                $locale
                            )
                        );
                    }

                    $translations[$locale] = [
                        'name' => $title,
                        'title' => $title,
                        'description' => $description,
                        'link' => $link,
                    ];
                }

                return [
                    'source_key' => $sourceKey,
                    'sort_order' => $sourceKey,
                    'icon_source' => $this->resolveIconSource(
                        $sourceItem,
                        $sourceKey
                    ),
                    'translations' => $translations,
                ];
            })
            ->sortBy('sort_order')
            ->values();

        if ($items->isEmpty()) {
            throw new RuntimeException(
                'No service items were found in the OpenCart home.text_banner configuration.'
            );
        }

        return $items;
    }

    private function sourceSettings(): array
    {
        if (! Schema::hasTable(self::SOURCE_TABLE)) {
            throw new RuntimeException(
                sprintf(
                    'The source table %s does not exist.',
                    self::SOURCE_TABLE
                )
            );
        }

        if (! Schema::hasColumn(
            self::SOURCE_TABLE,
            self::SOURCE_DATA_COLUMN
        )) {
            throw new RuntimeException(
                sprintf(
                    'The source table %s does not contain the %s column.',
                    self::SOURCE_TABLE,
                    self::SOURCE_DATA_COLUMN
                )
            );
        }

        $rows = DB::table(self::SOURCE_TABLE)
            ->select(self::SOURCE_DATA_COLUMN)
            ->whereNotNull(self::SOURCE_DATA_COLUMN)
            ->get();

        if ($rows->isEmpty()) {
            throw new RuntimeException(
                sprintf(
                    'The source table %s does not contain any settings rows.',
                    self::SOURCE_TABLE
                )
            );
        }

        $decodeErrors = [];

        foreach ($rows as $index => $row) {
            $rawPayload = $row->{self::SOURCE_DATA_COLUMN} ?? null;

            try {
                $payload = $this->decodeSourcePayload($rawPayload);
            } catch (RuntimeException $runtimeException) {
                $decodeErrors[] = sprintf(
                    'Row %d: %s',
                    $index + 1,
                    $runtimeException->getMessage()
                );

                continue;
            }

            $settings = $this->findSettingsNode($payload);

            if ($settings !== null) {
                return $settings;
            }
        }

        throw new RuntimeException(
            sprintf(
                'Unable to resolve home.text_banner from %s.%s. %s',
                self::SOURCE_TABLE,
                self::SOURCE_DATA_COLUMN,
                $decodeErrors !== []
                    ? implode(' | ', $decodeErrors)
                    : 'The JSON was decoded, but the expected configuration path was not found.'
            )
        );
    }

    private function decodeSourcePayload(mixed $rawPayload): array
    {
        if (is_array($rawPayload)) {
            return $rawPayload;
        }

        if (is_object($rawPayload)) {
            return (array) $rawPayload;
        }

        if (! is_string($rawPayload)) {
            throw new RuntimeException(
                'The settings payload is not a string, array, or object.'
            );
        }

        $candidate = preg_replace(
            '/^\xEF\xBB\xBF/',
            '',
            trim($rawPayload)
        ) ?? trim($rawPayload);

        if ($candidate === '') {
            throw new RuntimeException(
                'The settings payload is empty.'
            );
        }

        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
                $decodedPayload = json_decode(
                    $candidate,
                    true,
                    512,
                    JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE
                );
            } catch (JsonException $jsonException) {
                throw new RuntimeException(
                    sprintf(
                        'Invalid JSON payload: %s',
                        $jsonException->getMessage()
                    ),
                    previous: $jsonException
                );
            }

            if (is_array($decodedPayload)) {
                return $decodedPayload;
            }

            if (! is_string($decodedPayload)) {
                throw new RuntimeException(
                    'The decoded settings payload is not an array.'
                );
            }

            $candidate = trim($decodedPayload);
        }

        throw new RuntimeException(
            'The settings payload remained string-encoded after three decoding attempts.'
        );
    }

    private function findSettingsNode(
        array $payload,
        int $depth = 0
    ): ?array {
        if (is_array(data_get($payload, 'home.text_banner'))) {
            return $payload;
        }

        if ($depth >= 3) {
            return null;
        }

        $storeSettings = $payload[self::SOURCE_STORE_ID]
            ?? $payload[(string) self::SOURCE_STORE_ID]
            ?? null;

        if (is_array($storeSettings)) {
            $resolvedStoreSettings = $this->findSettingsNode(
                $storeSettings,
                $depth + 1
            );

            if ($resolvedStoreSettings !== null) {
                return $resolvedStoreSettings;
            }
        }

        foreach ($payload as $value) {
            if (! is_array($value) || $value === $storeSettings) {
                continue;
            }

            $resolvedSettings = $this->findSettingsNode(
                $value,
                $depth + 1
            );

            if ($resolvedSettings !== null) {
                return $resolvedSettings;
            }
        }

        return null;
    }

    private function requiredSourceValue(
        array $sourceItem,
        string $field,
        int $sourceLanguageId,
        int $sourceKey
    ): string {
        $value = $this->nullIfEmpty(
            data_get(
                $sourceItem,
                "{$field}.{$sourceLanguageId}"
            )
        );

        if ($value !== null) {
            return html_entity_decode(
                $value,
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );
        }

        throw new RuntimeException(
            sprintf(
                'The required field %s is missing for source item %d and language ID %d.',
                $field,
                $sourceKey,
                $sourceLanguageId
            )
        );
    }

    private function resolveIconSource(
        array $sourceItem,
        int $sourceKey
    ): string {
        foreach (
            array_values(self::SOURCE_LANGUAGE_ID_BY_LOCALE)
            as $sourceLanguageId
        ) {
            $iconSource = $this->nullIfEmpty(
                data_get(
                    $sourceItem,
                    "img.{$sourceLanguageId}"
                )
            );

            if ($iconSource !== null) {
                return html_entity_decode(
                    $iconSource,
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                );
            }
        }

        throw new RuntimeException(
            sprintf(
                'The icon source is missing for service item %d.',
                $sourceKey
            )
        );
    }

    private function normalizeLink(string $sourceLink): ?string
    {
        $path = parse_url(
            html_entity_decode(
                trim($sourceLink),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            ),
            PHP_URL_PATH
        );

        if (! is_string($path)) {
            return null;
        }

        $segments = collect(explode('/', trim($path, '/')))
            ->map(
                fn (string $segment): string => trim(
                    rawurldecode($segment)
                )
            )
            ->filter()
            ->values();

        if (
            $segments->isNotEmpty()
            && in_array(
                strtolower((string) $segments->first()),
                array_keys(self::SOURCE_LANGUAGE_ID_BY_LOCALE),
                true
            )
        ) {
            $segments->shift();
        }

        $normalizedLink = $segments
            ->map(function (string $segment): ?string {
                $slug = Str::slug($segment);

                return $slug !== '' ? $slug : null;
            })
            ->filter()
            ->implode('/');

        return $this->nullIfEmpty($normalizedLink);
    }

    private function resolveExistingMenu(array $item): ?Menu
    {
        $links = collect($item['translations'])
            ->pluck('link')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($links !== []) {
            $translation = MenuTranslation::query()
                ->whereIn(
                    'locale',
                    array_keys(self::SOURCE_LANGUAGE_ID_BY_LOCALE)
                )
                ->whereIn('link', $links)
                ->whereHas('menu', function ($query): void {
                    $query
                        ->where('type', 'content')
                        ->where('view_type', self::TARGET_VIEW_TYPE);
                })
                ->with('menu')
                ->first();

            if ($translation !== null) {
                return $translation->menu;
            }
        }

        $names = collect($item['translations'])
            ->pluck('name')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($names === []) {
            return null;
        }

        return MenuTranslation::query()
            ->whereIn(
                'locale',
                array_keys(self::SOURCE_LANGUAGE_ID_BY_LOCALE)
            )
            ->whereIn('name', $names)
            ->whereHas('menu', function ($query): void {
                $query
                    ->where('type', 'content')
                    ->where('view_type', self::TARGET_VIEW_TYPE);
            })
            ->with('menu')
            ->first()
            ?->menu;
    }

    private function syncMenuTranslations(
        Menu $menu,
        array $item
    ): void {
        foreach (
            $item['translations']
            as $locale => $translation
        ) {
            MenuTranslation::query()->updateOrCreate(
                [
                    'menu_id' => $menu->id,
                    'locale' => $locale,
                ],
                [
                    'name' => $translation['name'],
                    'title' => $translation['title'],
                    'description' => $translation['description'],
                    'link' => $translation['link'],
                    'meta_title' => null,
                    'meta_description' => null,
                    'meta_keywords' => null,
                ]
            );
        }
    }

    private function resolveOrUploadIcon(
        array $item,
        ?Menu $existingMenu
    ): string {
        $existingIconImage = $this->nullIfEmpty(
            $existingMenu?->icon_image
        );

        if (
            $existingIconImage !== null
            && Storage::disk('public')->exists($existingIconImage)
        ) {
            return $existingIconImage;
        }

        return $this->uploadSourceIcon(
            source: $item['icon_source'],
            directory: sprintf(
                'menus/services/%d',
                $item['source_key']
            )
        );
    }

    private function uploadSourceIcon(
        string $source,
        string $directory
    ): string {
        $fullPath = $this->sourceAbsolutePath($source);

        if (! File::isFile($fullPath)) {
            throw new RuntimeException(
                sprintf(
                    'The source service icon does not exist: %s',
                    $fullPath
                )
            );
        }

        $cacheKey = md5($fullPath . '|' . $directory);

        if (isset($this->uploadedAssets[$cacheKey])) {
            return $this->uploadedAssets[$cacheKey];
        }

        $uploadedFile = new UploadedFile(
            $fullPath,
            basename($fullPath),
            File::mimeType($fullPath) ?: 'application/octet-stream',
            null,
            true
        );

        $storedPath = $this->fileUploadService->storeRaw(
            $uploadedFile,
            $directory
        );

        if ($this->nullIfEmpty($storedPath) === null) {
            throw new RuntimeException(
                sprintf(
                    'Unable to upload the source service icon: %s',
                    $fullPath
                )
            );
        }

        $this->uploadedAssets[$cacheKey] = $storedPath;

        return $storedPath;
    }

    private function sourceIconExists(string $source): bool
    {
        return File::isFile(
            $this->sourceAbsolutePath($source)
        );
    }

    private function sourceAbsolutePath(string $relativePath): string
    {
        $path = parse_url(
            html_entity_decode(
                trim($relativePath),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            ),
            PHP_URL_PATH
        );

        $normalizedPath = is_string($path)
            ? $path
            : $relativePath;

        return public_path(
            'uploads/opencart/' . ltrim($normalizedPath, '/')
        );
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
