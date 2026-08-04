<?php

namespace Modules\Gallery\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Gallery\Models\GalleryAlbumItem;
use Modules\Gallery\Models\GalleryAlbumItemTranslation;
use Modules\Gallery\Models\GalleryAlbumTranslation;

class GalleryAlbumItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var GalleryAlbumItem $item */
        $item = $this->resource;

        $translation = $this->resolveItemTranslation($request, $item);
        $albumTranslation = $this->resolveAlbumTranslation($request, $item);

        return [
            'id' => (int) $item->id,
            'album_id' => (int) $item->gallery_album_id,
            'album_name' => $albumTranslation?->name ?? '',
            'type' => (string) $item->type,
            'title' => $translation?->title ?? '',
            'description' => $translation?->description ?? '',
            'file_path' => $item->file_path,
            'file_url' => $item->file_url,
            'video_url' => $item->video_url,
            'publication' => (bool) $item->publication,
            'is_active' => (bool) $item->is_active,
            'sort_order' => (int) $item->sort_order,
        ];
    }

    private function resolveItemTranslation(Request $request, GalleryAlbumItem $item): ?GalleryAlbumItemTranslation
    {
        $locale = (string) $request->attributes->get('api_locale', app()->getLocale());
        $fallbackLocale = (string) $request->attributes->get('api_fallback_locale', config('app.fallback_locale'));

        $translations = $item->translations ?? collect();

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

    private function resolveAlbumTranslation(Request $request, GalleryAlbumItem $item): ?GalleryAlbumTranslation
    {
        $locale = (string) $request->attributes->get('api_locale', app()->getLocale());
        $fallbackLocale = (string) $request->attributes->get('api_fallback_locale', config('app.fallback_locale'));

        $translations = $item->album?->translations ?? collect();

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
