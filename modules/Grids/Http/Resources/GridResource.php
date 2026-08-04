<?php

namespace Modules\Grids\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Menu\Support\LocalePicker;
use Modules\Product\Http\Resources\Api\Product\VariationResource;

class GridResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        $fallbackLocale = (string) config('app.fallback_locale', $locale);

        return [
            'id' => (int) $this->id,
            'menu_id' => (int) $this->menu_id,
            'banner' => $this->banner_url,
            'name' => LocalePicker::pickString($this->name, $locale, $fallbackLocale),
            'slug' => LocalePicker::pickString($this->slug, $locale, $fallbackLocale),
            'content' => LocalePicker::pickString($this->content, $locale, $fallbackLocale),
            'location_or_group' => LocalePicker::pickString($this->location_or_group, $locale, $fallbackLocale),
            'meta_title' => LocalePicker::pickString($this->meta_title, $locale, $fallbackLocale),
            'meta_description' => LocalePicker::pickString($this->meta_description, $locale, $fallbackLocale),
            'meta_keywords' => LocalePicker::pickString($this->meta_keywords, $locale, $fallbackLocale),
            'datetime1' => $this->datetime1?->toISOString(),
            'datetime2' => $this->datetime2?->toISOString(),
            'is_active' => (bool) $this->is_active,
            'sort_order' => (int) $this->sort_order,
            'main_photo' => $this->main_image_url,
            'images' => $this->whenLoaded('media', function () {
                return $this->media->map(function ($media) {
                    return [
                        'id' => (int) $media->id,
                        'path' => $media->path,
                        'url' => $media->url,
                        'type' => $media->type ?? 'image',
                        'original_name' => $media->original_name,
                        'is_main' => (bool) $media->is_main,
                        'sort_order' => (int) $media->sort_order,
                    ];
                })->values();
            }, []),
            'related_products' => $this->whenLoaded('relatedProductItems', function () use ($request) {
                return $this->relatedProductItems
                    ->map(function ($item) use ($request) {
                        if (!$item->variation) {
                            return null;
                        }

                        return [
                            'id' => (int) $item->id,
                            'product_id' => (int) $item->product_id,
                            'product_variation_id' => (int) $item->product_variation_id,
                            'sort_order' => (int) $item->sort_order,
                            'variation' => (new VariationResource($this->mapVariationForResource($item->variation)))->toArray($request),
                        ];
                    })
                    ->filter()
                    ->values();
            }, []),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    private function mapVariationForResource($variation): array
    {
        $translation = $variation->translations?->first();

        $gallery = collect($variation->media ?? [])
            ->map(function ($media) {
                return [
                    'id' => (int) $media->id,
                    'url' => $media->url,
                    'sort_order' => (int) ($media->sort_order ?? 0),
                    'is_main' => (bool) ($media->is_main ?? false),
                ];
            })
            ->values()
            ->all();

        $filters = collect($variation->filterValues ?? [])
            ->map(function ($value) {
                $translation = $value->translations?->first();
                $filter = $value->filter;
                $filterTranslation = $filter?->translations?->first();

                return [
                    'id' => (int) $value->id,
                    'name' => $translation?->name ?? '',
                    'filter_id' => (int) ($filter?->id ?? 0),
                    'filter_name' => $filterTranslation?->name ?? '',
                ];
            })
            ->values()
            ->all();

        return [
            'variation_id' => (int) $variation->id,
            'variation_uuid' => $variation->uuid,
            'name' => $translation?->name ?? '',
            'slug' => $translation?->slug ?? '',
            'stock' => (int) ($variation->stock ?? 0),
            'price' => $variation->price,
            'old_price' => $variation->old_price,
            'discount_price' => $variation->discount_price,
            'meta_title' => $translation?->meta_title,
            'meta_description' => $translation?->meta_description,
            'meta_keywords' => $translation?->meta_keywords,
            'main_image_path' => $variation->mainMedia?->path ?? $variation->media?->firstWhere('is_main', true)?->path ?? $variation->media?->first()?->path,
            'gallery' => $gallery,
            'filters' => $filters,
        ];
    }
}
