<?php

namespace Modules\Compare\Http\Resources;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Cache;
use Modules\Product\Http\Resources\Api\Product\VariationResource;
use Modules\Product\Models\Filter\ProductFilter;
use Modules\Product\Models\Filter\ProductFilterTranslation;
use Modules\Product\Models\Filter\ProductFilterValue;
use Modules\Product\Models\Filter\ProductFilterValueTranslation;
use Modules\Product\Models\Variation\ProductVariation;

final class CompareResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $variation = $this->relationLoaded('variation') ? $this->variation : null;

        return [
            'id' => (int) $this->id,
            'customer_id' => $this->customer_id !== null ? (int) $this->customer_id : null,
            'token' => $this->token !== null ? (string) $this->token : null,
            'product_variation_id' => (int) $this->product_variation_id,
            'merged_at' => $this->merged_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'variation' => $variation instanceof ProductVariation
                ? new VariationResource($this->variationPayload($variation))
                : null,
        ];
    }

    private function variationPayload(ProductVariation $variation): array
    {
        $languageId = $this->languageId();
        $translation = $this->pickVariationTranslation($variation->translations ?? null, $languageId);

        $mainImagePath = '';
        $gallery = [];

        if ($variation->relationLoaded('media') && $variation->media && $variation->media->isNotEmpty()) {
            foreach ($variation->media as $media) {
                $path = (string) ($media->path ?? '');

                $gallery[] = [
                    'id' => (int) $media->id,
                    'sort_order' => (int) $media->sort_order,
                    'is_main' => (bool) $media->is_main,
                    'path' => $path !== '' ? (string) $media->url : null,
                ];

                if ($mainImagePath === '' && $path !== '') {
                    $mainImagePath = $path;
                }
            }
        }

        return [
            'variation_id' => (int) $variation->id,
            'variation_uuid' => $variation->uuid ?? null,
            'name' => (string) ($translation?->name ?? ''),
            'slug' => (string) ($translation?->slug ?? ''),
            'meta_title' => $translation?->meta_title,
            'meta_description' => $translation?->meta_description,
            'meta_keywords' => $translation?->meta_keywords,
            'stock' => (int) ($variation->stock ?? 0),
            'price' => (float) ($variation->price ?? 0),
            'old_price' => $variation->old_price !== null ? (float) $variation->old_price : null,
            'discount_price' => $variation->discount_price !== null ? (float) $variation->discount_price : null,
            'main_image_path' => $mainImagePath,
            'gallery' => $gallery,
            'filters' => $this->filtersPayload($variation, $languageId),
        ];
    }

    private function filtersPayload(ProductVariation $variation, int $languageId): array
    {
        if (! $variation->relationLoaded('filterValues') || ! $variation->filterValues) {
            return [];
        }

        $groupedFilters = [];

        foreach ($this->sortedFilterValues($variation) as $value) {
            if (! $value instanceof ProductFilterValue) {
                continue;
            }

            $filter = $value->relationLoaded('filter') ? $value->filter : null;

            if (! $filter instanceof ProductFilter) {
                continue;
            }

            if (! $this->isActive($filter->status ?? null) || ! $this->isActive($value->status ?? null)) {
                continue;
            }

            $filterId = (int) $filter->id;
            $filterTranslation = $this->pickFilterTranslation($filter->translations ?? null, $languageId);
            $valueTranslation = $this->pickValueTranslation($value->translations ?? null, $languageId);

            if (! isset($groupedFilters[$filterId])) {
                $groupedFilters[$filterId] = [
                    'filter_id' => $filterId,
                    'name' => (string) ($filterTranslation?->name ?? ''),
                    'slug' => (string) ($filterTranslation?->slug ?? ''),
                    'input_type' => (string) ($filter->input_type ?? 'single'),
                    'is_color_filter' => (bool) ($filter->is_color_filter ?? false),
                    'show_in_sidebar' => (bool) ($filter->show_in_sidebar ?? false),
                    'is_required' => (bool) ($filter->is_required ?? false),
                    'is_clickable' => (bool) ($filter->is_clickable ?? false),
                    'image' => $filter->image,
                    'meta_title' => $filterTranslation?->meta_title,
                    'meta_description' => $filterTranslation?->meta_description,
                    'meta_keywords' => $filterTranslation?->meta_keywords,
                    'values' => [],
                ];
            }

            $groupedFilters[$filterId]['values'][] = [
                'value_id' => (int) $value->id,
                'name' => (string) ($valueTranslation?->name ?? ''),
                'slug' => (string) ($valueTranslation?->slug ?? ''),
                'count' => 0,
                'color' => $value->color,
                'image' => $value->image,
                'meta_title' => $valueTranslation?->meta_title,
                'meta_description' => $valueTranslation?->meta_description,
                'meta_keywords' => $valueTranslation?->meta_keywords,
            ];
        }

        return array_values($groupedFilters);
    }

    private function sortedFilterValues(ProductVariation $variation): SupportCollection
    {
        return $variation->filterValues->sort(function ($firstValue, $secondValue): int {
            $firstFilter = $firstValue->relationLoaded('filter') ? $firstValue->filter : null;
            $secondFilter = $secondValue->relationLoaded('filter') ? $secondValue->filter : null;

            return [
                    (int) ($firstFilter?->sort_order ?? 0),
                    (int) ($firstFilter?->id ?? 0),
                    (int) ($firstValue->sort_order ?? 0),
                    (int) ($firstValue->id ?? 0),
                ] <=> [
                    (int) ($secondFilter?->sort_order ?? 0),
                    (int) ($secondFilter?->id ?? 0),
                    (int) ($secondValue->sort_order ?? 0),
                    (int) ($secondValue->id ?? 0),
                ];
        })->values();
    }

    private function pickVariationTranslation(?SupportCollection $translations, int $languageId)
    {
        if (! $translations || $translations->isEmpty()) {
            return null;
        }

        return $translations->firstWhere('language_id', $languageId)
            ?: $translations->sortBy('language_id')->first();
    }

    private function pickFilterTranslation(?SupportCollection $translations, int $languageId): ?ProductFilterTranslation
    {
        if (! $translations || $translations->isEmpty()) {
            return null;
        }

        $translation = $translations->firstWhere('language_id', $languageId)
            ?: $translations->sortBy('language_id')->first();

        return $translation instanceof ProductFilterTranslation ? $translation : null;
    }

    private function pickValueTranslation(?SupportCollection $translations, int $languageId): ?ProductFilterValueTranslation
    {
        if (! $translations || $translations->isEmpty()) {
            return null;
        }

        $translation = $translations->firstWhere('language_id', $languageId)
            ?: $translations->sortBy('language_id')->first();

        return $translation instanceof ProductFilterValueTranslation ? $translation : null;
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

    private function isActive(mixed $status): bool
    {
        return $status === 'Active' || $status === 1 || $status === true || $status === '1';
    }
}
