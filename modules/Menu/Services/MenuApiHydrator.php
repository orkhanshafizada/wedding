<?php

namespace Modules\Menu\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Menu\Enums\MenuType;
use Modules\Menu\Models\Menu;
use Modules\Menu\Models\MenuTranslation;

class MenuApiHydrator
{
    public function hydrate(Menu $menu, string $locale, string $fallbackLocale): void
    {
        $menu->setAttribute('api_name', $this->resolveTranslatedValue($menu->translations, 'name', $locale, $fallbackLocale, ''));
        $menu->setAttribute('api_title', $this->resolveTranslatedValue($menu->translations, 'title', $locale, $fallbackLocale));
        $menu->setAttribute('api_description', $this->resolveTranslatedValue($menu->translations, 'description', $locale, $fallbackLocale));
        $menu->setAttribute('api_link', $this->resolveTranslatedValue($menu->translations, 'link', $locale, $fallbackLocale));
        $menu->setAttribute('api_meta_title', $this->resolveTranslatedValue($menu->translations, 'meta_title', $locale, $fallbackLocale));
        $menu->setAttribute('api_meta_description', $this->resolveTranslatedValue($menu->translations, 'meta_description', $locale, $fallbackLocale));
        $menu->setAttribute('api_meta_keywords', $this->resolveTranslatedValue($menu->translations, 'meta_keywords', $locale, $fallbackLocale));

        $type = $menu->type instanceof MenuType ? $menu->type->value : (string) $menu->type;
        $menu->setAttribute('api_type', $type);
    }

    private function resolveTranslatedValue(Collection $translations, string $field, string $locale, string $fallbackLocale, ?string $default = null): ?string
    {
        $value = $this->extractTranslationValue($translations->firstWhere('locale', $locale), $field);
        if ($value !== null) {
            return $value;
        }

        if ($fallbackLocale !== $locale) {
            $value = $this->extractTranslationValue($translations->firstWhere('locale', $fallbackLocale), $field);
            if ($value !== null) {
                return $value;
            }
        }

        foreach ($translations as $translation) {
            $value = $this->extractTranslationValue($translation, $field);
            if ($value !== null) {
                return $value;
            }
        }

        return $default;
    }

    private function extractTranslationValue(?MenuTranslation $translation, string $field): ?string
    {
        if (! $translation) {
            return null;
        }

        $value = $translation->{$field} ?? null;

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
