<?php

namespace Modules\Gallery\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Gallery\Models\GalleryAlbum;
use Modules\Gallery\Models\GalleryAlbumTranslation;

class GalleryAlbumResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var GalleryAlbum $album */
        $album = $this->resource;

        $translation = $this->resolveTranslation($request, $album);

        return [
            'id' => (int) $album->id,
            'name' => $translation?->name ?? '',
            'show_album' => (bool) $album->show_album,
            'is_active' => (bool) $album->is_active,
            'cover_image' => $album->cover_image_url,
            'sort_order' => (int) $album->sort_order,
            'items_count' => (int) ($album->items?->count() ?? 0),
            'items' => GalleryAlbumItemResource::collection($album->items)->resolve($request),
        ];
    }

    private function resolveTranslation(Request $request, GalleryAlbum $album): ?GalleryAlbumTranslation
    {
        $locale = (string) $request->attributes->get('api_locale', app()->getLocale());
        $fallbackLocale = (string) $request->attributes->get('api_fallback_locale', config('app.fallback_locale'));

        $translations = $album->translations ?? collect();

        $current = $translations->firstWhere('locale', $locale);
        if ($current) {
            return $current;
        }

        if ($fallbackLocale !== $locale) {
            $fallback = $translations->firstWhere('locale', $fallbackLocale);
            if ($fallback) {
                return $fallback;
            }
        }

        return $translations->first();
    }
}
