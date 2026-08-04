<?php

namespace Modules\Product\Services\Api;

use Illuminate\Support\Collection;
use Modules\Menu\Enums\MenuType;
use Modules\Menu\Models\Menu;
use Modules\Menu\Models\MenuTranslation;

class CategoryMenuTreeService
{
    public function getActiveCategoryTree(): Collection
    {
        $categoryMenus = Menu::query()
            ->select([
                'id',
                'parent_id',
                'sort_order',
                'icon',
                'icon_image',
                'main_image',
            ])
            ->where('type', MenuType::CATEGORIES->value)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($categoryMenus->isEmpty()) {
            return collect();
        }

        $translationsByMenuId = $this->getTranslationsByMenuId($categoryMenus->pluck('id'));

        foreach ($categoryMenus as $categoryMenu) {
            $translation = $this->resolveTranslation($translationsByMenuId->get((int) $categoryMenu->id, collect()));

            $categoryMenu->setAttribute('api_name', (string) ($translation?->name ?? ''));
            $categoryMenu->setAttribute('api_link', $this->normalizeLink($translation?->link));
            $categoryMenu->setRelation('children', collect());
        }

        return $this->buildTree($categoryMenus);
    }

    private function getTranslationsByMenuId(Collection $categoryMenuIds): Collection
    {
        return MenuTranslation::query()
            ->select([
                'id',
                'menu_id',
                'locale',
                'name',
                'link',
            ])
            ->whereIn('menu_id', $categoryMenuIds->values()->all())
            ->orderBy('id')
            ->get()
            ->groupBy('menu_id');
    }

    private function resolveTranslation(Collection $translations): ?MenuTranslation
    {
        if ($translations->isEmpty()) {
            return null;
        }

        $localeCandidates = $this->getLocaleCandidates();

        foreach ($localeCandidates as $localeCandidate) {
            $translation = $translations->first(function (MenuTranslation $translation) use ($localeCandidate): bool {
                return trim((string) $translation->locale) === $localeCandidate;
            });

            if ($translation instanceof MenuTranslation) {
                return $translation;
            }
        }

        return $translations->first();
    }

    private function getLocaleCandidates(): array
    {
        $currentLocale = trim((string) app()->getLocale());
        $fallbackLocale = trim((string) config('app.locale'));

        return collect([
            $currentLocale,
            str_replace('_', '-', $currentLocale),
            str_replace('-', '_', $currentLocale),
            $this->getShortLocale($currentLocale),
            $fallbackLocale,
            str_replace('_', '-', $fallbackLocale),
            str_replace('-', '_', $fallbackLocale),
            $this->getShortLocale($fallbackLocale),
        ])
            ->filter(fn (?string $locale): bool => is_string($locale) && trim($locale) !== '')
            ->map(fn (string $locale): string => trim($locale))
            ->unique()
            ->values()
            ->all();
    }

    private function getShortLocale(string $locale): ?string
    {
        $locale = trim($locale);

        if ($locale === '') {
            return null;
        }

        $locale = str_replace('_', '-', $locale);

        return explode('-', $locale)[0] ?? null;
    }

    private function buildTree(Collection $categoryMenus): Collection
    {
        $categoryMenusById = $categoryMenus->keyBy('id');
        $rootCategoryMenus = collect();

        foreach ($categoryMenus as $categoryMenu) {
            if ($categoryMenu->parent_id === null) {
                $rootCategoryMenus->push($categoryMenu);

                continue;
            }

            $parentCategoryMenu = $categoryMenusById->get((int) $categoryMenu->parent_id);

            if (! $parentCategoryMenu instanceof Menu) {
                $rootCategoryMenus->push($categoryMenu);

                continue;
            }

            $parentCategoryMenu->children->push($categoryMenu);
        }

        return $rootCategoryMenus->values();
    }

    private function normalizeLink(?string $link): ?string
    {
        $link = trim((string) $link);

        if ($link === '') {
            return null;
        }

        return $link;
    }
}
