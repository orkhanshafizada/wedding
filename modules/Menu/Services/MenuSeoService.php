<?php

namespace Modules\Menu\Services;

use App\Models\Language;
use App\Support\Settings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Modules\Menu\Models\Menu;
use Modules\Menu\Models\MenuTranslation;

class MenuSeoService
{
    protected static ?bool $gdAvailable = null;

    public function buildMenuSeo(
        Menu $menu,
        string $locale,
        array $itemLinksByLocale = [],
        array $overrides = [],
        array $query = []
    ): array {
        $activeLanguages = $this->getActiveLanguages();
        $activeLanguageCodes = $activeLanguages
            ->pluck('code')
            ->filter(fn ($code) => is_string($code) && trim($code) !== '')
            ->map(fn ($code) => trim((string) $code))
            ->values()
            ->all();

        $defaultLocale = $this->resolveDefaultLocale($activeLanguages);

        if (!in_array($locale, $activeLanguageCodes, true)) {
            $locale = $defaultLocale;
        }

        $menuLinksByLocale = $this->resolveMenuLinksByLocale($menu, $activeLanguageCodes);
        $fallbackMenuLink = $this->firstNonNullValue($menuLinksByLocale);

        $canonical = $this->buildLocalizedUrl(
            locale: $locale,
            menuLink: $menuLinksByLocale[$locale] ?? $fallbackMenuLink,
            itemLink: $this->resolveLocalizedItemLink($itemLinksByLocale, $locale),
            query: $query
        );

        $alternates = [];

        foreach ($activeLanguageCodes as $languageCode) {
            $menuLink = $menuLinksByLocale[$languageCode] ?? $fallbackMenuLink;
            $itemLink = $this->resolveLocalizedItemLink($itemLinksByLocale, $languageCode);

            if ($itemLinksByLocale !== [] && $itemLink === null) {
                continue;
            }

            $url = $this->buildLocalizedUrl(
                locale: $languageCode,
                menuLink: $menuLink,
                itemLink: $itemLink,
                query: $query
            );

            if ($url === null) {
                continue;
            }

            $alternates[] = [
                'locale' => $languageCode,
                'hreflang' => $languageCode,
                'url' => $url,
            ];
        }

        $xDefaultItemLink = $this->resolveLocalizedItemLink($itemLinksByLocale, $defaultLocale);
        if ($itemLinksByLocale !== [] && $xDefaultItemLink === null) {
            $xDefaultItemLink = $this->firstNonNullValue($itemLinksByLocale);
        }

        $xDefault = $this->buildLocalizedUrl(
            locale: $defaultLocale,
            menuLink: $menuLinksByLocale[$defaultLocale] ?? $fallbackMenuLink,
            itemLink: $xDefaultItemLink,
            query: $query
        );

        $title = $this->normalizeNullableString($overrides['title'] ?? $menu->getAttribute('api_title') ?? $menu->getAttribute('api_name'));
        $description = $this->normalizeNullableString($overrides['description'] ?? $menu->getAttribute('api_description'));
        $metaTitle = $this->normalizeNullableString($overrides['meta_title'] ?? $menu->getAttribute('api_meta_title') ?? $title);
        $metaDescription = $this->normalizeNullableString($overrides['meta_description'] ?? $menu->getAttribute('api_meta_description') ?? $description);
        $metaKeywords = $this->normalizeNullableString($overrides['meta_keywords'] ?? $menu->getAttribute('api_meta_keywords'));
        $robots = $this->normalizeNullableString($overrides['robots'] ?? Settings::get('seo', 'robots', 'index,follow')) ?? 'index,follow';
        $ogType = $this->normalizeNullableString($overrides['og_type'] ?? Settings::get('og', 'type', 'website')) ?? 'website';
        $siteName = $this->resolveSiteName($locale, $activeLanguages);
        $imageUrl = $this->resolveSeoImageUrl($overrides['image'] ?? null);
        $imageAlt = $this->normalizeNullableString($overrides['image_alt'] ?? $metaTitle ?? $title ?? $siteName);
        $imageMeta = $this->resolveImageMeta($imageUrl);
        $publishedTime = $this->normalizeDateTime($overrides['published_time'] ?? null);
        $modifiedTime = $this->normalizeDateTime($overrides['modified_time'] ?? null);
        $articleSection = $this->normalizeNullableString($overrides['article_section'] ?? $menu->getAttribute('api_name'));
        $structuredType = $this->normalizeNullableString($overrides['structured_type'] ?? 'WebPage') ?? 'WebPage';

        $structuredData = array_filter([
            '@context' => 'https://schema.org',
            '@type' => $structuredType,
            'url' => $canonical,
            'inLanguage' => $locale,
            'name' => $metaTitle ?? $title,
            'description' => $metaDescription ?? $description,
            'image' => $imageUrl,
            'datePublished' => $publishedTime,
            'dateModified' => $modifiedTime,
        ], fn ($value) => $value !== null && $value !== '');

        return [
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'meta_keywords' => $metaKeywords,
            'robots' => $robots,
            'canonical' => $canonical,
            'alternates' => $alternates,
            'x_default' => $xDefault,
            'open_graph' => [
                'type' => $ogType,
                'site_name' => $siteName,
                'url' => $canonical,
                'title' => $metaTitle ?? $title,
                'description' => $metaDescription ?? $description,
                'locale' => $this->resolveOgLocale($locale),
                'locale_alternate' => array_values(array_filter(
                    array_map(fn (string $languageCode): string => $this->resolveOgLocale($languageCode), $activeLanguageCodes),
                    fn (string $ogLocale): bool => $ogLocale !== $this->resolveOgLocale($locale)
                )),
                'image' => $imageUrl,
                'image_alt' => $imageAlt,
                'image_type' => $imageMeta['type'],
                'image_width' => $imageMeta['width'],
                'image_height' => $imageMeta['height'],
            ],
            'twitter' => [
                'card' => $this->normalizeNullableString(Settings::get('og', 'twitter_card', 'summary_large_image')) ?? 'summary_large_image',
                'site' => $this->normalizeNullableString(Settings::get('og', 'twitter_site', '')),
                'title' => $metaTitle ?? $title,
                'description' => $metaDescription ?? $description,
                'image' => $imageUrl,
                'image_alt' => $imageAlt,
            ],
            'article' => [
                'published_time' => $publishedTime,
                'modified_time' => $modifiedTime,
                'section' => $articleSection,
            ],
            'structured_data' => $structuredData,
        ];
    }

    public function appendPaginationSeo(
        array $seo,
        Menu $menu,
        string $locale,
        int $page,
        int $lastPage,
        array $itemLinksByLocale = []
    ): array {
        if ($lastPage <= 1) {
            return $seo;
        }

        if ($page > 1) {
            $prevQuery = $page - 1 > 1 ? ['page' => $page - 1] : [];

            $seo['prev'] = $this->buildMenuSeo(
                menu: $menu,
                locale: $locale,
                itemLinksByLocale: $itemLinksByLocale,
                query: $prevQuery
            )['canonical'] ?? null;
        }

        if ($page < $lastPage) {
            $seo['next'] = $this->buildMenuSeo(
                menu: $menu,
                locale: $locale,
                itemLinksByLocale: $itemLinksByLocale,
                query: ['page' => $page + 1]
            )['canonical'] ?? null;
        }

        return $seo;
    }

    public function getActiveLanguageCodes(): array
    {
        return $this->getActiveLanguages()
            ->pluck('code')
            ->filter(fn ($code) => is_string($code) && trim($code) !== '')
            ->map(fn ($code) => trim((string) $code))
            ->values()
            ->all();
    }

    public function resolveItemSeoDefaults(
        Menu $menu,
        string $locale,
        ?string $title = null,
        ?string $description = null,
        ?string $metaTitle = null,
        ?string $metaDescription = null,
        ?string $metaKeywords = null,
        ?string $image = null,
        ?string $articleSection = null,
        ?string $publishedTime = null,
        ?string $modifiedTime = null,
        string $ogType = 'article',
        string $structuredType = 'Article'
    ): array {
        return [
            'title' => $title,
            'description' => $description,
            'meta_title' => $metaTitle ?: $title ?: $menu->getAttribute('api_meta_title'),
            'meta_description' => $metaDescription ?: $description ?: $menu->getAttribute('api_meta_description'),
            'meta_keywords' => $metaKeywords ?: $menu->getAttribute('api_meta_keywords'),
            'image' => $image,
            'image_alt' => $metaTitle ?: $title ?: $menu->getAttribute('api_name'),
            'article_section' => $articleSection ?: $menu->getAttribute('api_name'),
            'published_time' => $publishedTime,
            'modified_time' => $modifiedTime,
            'og_type' => $ogType,
            'structured_type' => $structuredType,
        ];
    }

    protected function getActiveLanguages(): Collection
    {
        return Language::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'code', 'is_default_site']);
    }

    protected function resolveDefaultLocale(Collection $activeLanguages): string
    {
        $defaultLanguageId = (int) Settings::get('system', 'default_language_id', 0);

        if ($defaultLanguageId > 0) {
            $defaultLanguage = $activeLanguages->firstWhere('id', $defaultLanguageId);
            if ($defaultLanguage && is_string($defaultLanguage->code) && trim($defaultLanguage->code) !== '') {
                return trim((string) $defaultLanguage->code);
            }
        }

        $siteDefaultLanguage = $activeLanguages->firstWhere('is_default_site', true);
        if ($siteDefaultLanguage && is_string($siteDefaultLanguage->code) && trim($siteDefaultLanguage->code) !== '') {
            return trim((string) $siteDefaultLanguage->code);
        }

        return (string) config('app.locale');
    }

    protected function resolveMenuLinksByLocale(Menu $menu, array $activeLanguageCodes): array
    {
        $translations = $menu->relationLoaded('translations')
            ? $menu->translations
            : $menu->translations()->get();

        $translationMap = $translations
            ->filter(function (MenuTranslation $translation): bool {
                return is_string($translation->locale) && trim($translation->locale) !== '';
            })
            ->keyBy(function (MenuTranslation $translation): string {
                return trim((string) $translation->locale);
            });

        $result = [];

        foreach ($activeLanguageCodes as $languageCode) {
            $translation = $translationMap->get($languageCode);

            if (!$translation) {
                continue;
            }

            $link = $this->normalizePathSegment($translation->link ?? null);

            if ($link === null && trim((string) ($translation->link ?? '')) === '/') {
                $result[$languageCode] = '';
                continue;
            }

            if ($link === null) {
                continue;
            }

            $result[$languageCode] = $link;
        }

        return $result;
    }

    protected function resolveLocalizedItemLink(array $itemLinksByLocale, string $locale): ?string
    {
        if ($itemLinksByLocale === []) {
            return null;
        }

        $itemLink = $itemLinksByLocale[$locale] ?? null;

        if ($itemLink === null) {
            return null;
        }

        return $this->normalizePathSegment($itemLink);
    }

    protected function buildLocalizedUrl(string $locale, ?string $menuLink, ?string $itemLink = null, array $query = []): ?string
    {
        $frontendUrl = rtrim((string) Settings::get('general', 'frontend_url', config('app.url')), '/');

        if ($frontendUrl === '') {
            return null;
        }

        $segments = [trim($locale, '/')];

        if ($menuLink !== null && $menuLink !== '') {
            $segments[] = trim($menuLink, '/');
        }

        if ($itemLink !== null && $itemLink !== '') {
            $segments[] = trim($itemLink, '/');
        }

        $url = $frontendUrl . '/' . implode('/', array_filter($segments, fn ($segment) => $segment !== ''));

        $normalizedQuery = [];
        foreach ($query as $key => $value) {
            if ($value === null || $value === '' || $value === 1 || $value === '1') {
                continue;
            }

            $normalizedQuery[$key] = $value;
        }

        if ($normalizedQuery !== []) {
            $url .= '?' . http_build_query($normalizedQuery);
        }

        return $url;
    }

    protected function resolveSiteName(string $locale, ?Collection $activeLanguages = null): ?string
    {
        $activeLanguages = $activeLanguages ?? $this->getActiveLanguages();
        $defaultLocale = $this->resolveDefaultLocale($activeLanguages);

        $siteTitleMap = Settings::get('general', 'site_title', []);

        if (!is_array($siteTitleMap)) {
            return $this->normalizeNullableString(config('app.name'));
        }

        $localeLanguage = $activeLanguages->firstWhere('code', $locale);
        if ($localeLanguage) {
            $localizedTitle = $siteTitleMap[(string) $localeLanguage->id] ?? null;
            $localizedTitle = $this->normalizeNullableString($localizedTitle);

            if ($localizedTitle !== null) {
                return $localizedTitle;
            }
        }

        $defaultLanguage = $activeLanguages->firstWhere('code', $defaultLocale);
        if ($defaultLanguage) {
            $defaultTitle = $siteTitleMap[(string) $defaultLanguage->id] ?? null;
            $defaultTitle = $this->normalizeNullableString($defaultTitle);

            if ($defaultTitle !== null) {
                return $defaultTitle;
            }
        }

        foreach ($siteTitleMap as $value) {
            $value = $this->normalizeNullableString($value);

            if ($value !== null) {
                return $value;
            }
        }

        return $this->normalizeNullableString(config('app.name'));
    }

    protected function resolveOgLocale(string $locale): string
    {
        return match (strtolower(trim($locale))) {
            'az' => 'az_AZ',
            'en' => 'en_US',
            'ru' => 'ru_RU',
            'tr' => 'tr_TR',
            default => str_replace('-', '_', $locale),
        };
    }

    protected function resolveSeoImageUrl(mixed $image): ?string
    {
        $imagePath = $this->normalizeNullableString($image);

        if ($imagePath === null) {
            $imagePath = $this->normalizeNullableString(Settings::get('og', 'image', null));
        }

        if ($imagePath === null) {
            $generalImages = Settings::get('general', 'images', []);
            $imagePath = $this->normalizeNullableString(is_array($generalImages) ? ($generalImages['default_image'] ?? null) : null);
        }

        if ($imagePath === null) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $imagePath)) {
            return $imagePath;
        }

        return Storage::disk('public')->url($imagePath);
    }

    protected function resolveImageMeta(?string $imageUrl): array
    {
        if ($imageUrl === null) {
            return [
                'type' => null,
                'width' => null,
                'height' => null,
            ];
        }

        $path = parse_url($imageUrl, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return [
                'type' => null,
                'width' => null,
                'height' => null,
            ];
        }

        $publicPrefix = '/storage/';
        if (!str_starts_with($path, $publicPrefix)) {
            return [
                'type' => null,
                'width' => null,
                'height' => null,
            ];
        }

        $storagePath = substr($path, strlen($publicPrefix));

        if (!Storage::disk('public')->exists($storagePath)) {
            return [
                'type' => null,
                'width' => null,
                'height' => null,
            ];
        }

        $mimeType = Storage::disk('public')->mimeType($storagePath);
        $absolutePath = Storage::disk('public')->path($storagePath);

        $dimensions = [
            'width' => null,
            'height' => null,
        ];

        if ($this->canReadImageSize() && is_file($absolutePath)) {
            $imageSize = @getimagesize($absolutePath);

            if (is_array($imageSize)) {
                $dimensions['width'] = isset($imageSize[0]) ? (int) $imageSize[0] : null;
                $dimensions['height'] = isset($imageSize[1]) ? (int) $imageSize[1] : null;
            }
        }

        return [
            'type' => $this->normalizeNullableString($mimeType),
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
        ];
    }

    protected function canReadImageSize(): bool
    {
        if (self::$gdAvailable !== null) {
            return self::$gdAvailable;
        }

        self::$gdAvailable = extension_loaded('gd') || extension_loaded('fileinfo');

        return self::$gdAvailable;
    }

    protected function normalizePathSegment(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if ($value === '/') {
            return '';
        }

        return trim($value, '/');
    }

    protected function normalizeNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function normalizeDateTime(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function firstNonNullValue(array $items): ?string
    {
        foreach ($items as $value) {
            if ($value === null) {
                continue;
            }

            return trim((string) $value);
        }

        return null;
    }
}
