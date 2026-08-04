<?php

namespace Modules\Gallery\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Gallery\Models\GalleryAlbum;

class GalleryApiController extends BaseApiController
{
    /**
     * GET /api/v1/gallery/{menuId}
     *
     * Behavior:
     * - If menu has albums with show_album=1 => return albums list
     * - If menu has albums with show_album=0 => return items directly (merged)
     */
    public function index(Request $request, $menuId): JsonResponse
    {
        try {
            $menuIdInt = (int) $menuId;

            $albums = GalleryAlbum::query()
                ->with(['translations', 'items.translations'])
                ->where('menu_id', $menuIdInt)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            if ($albums->isEmpty()) {
                return $this->responseService->success([], __('gallery.albums_loaded'));
            }

            $locale = app()->getLocale();

            // If any album is configured to show album list, return albums.
            $shouldShowAlbums = $albums->contains(fn ($a) => (bool) $a->show_album);
            if ($shouldShowAlbums) {
                $data = $albums
                    ->where('show_album', true)
                    ->values()
                    ->map(function ($album) use ($locale) {
                        $name = $album->translations->firstWhere('locale', $locale)?->name
                            ?? $album->translations->first()?->name
                            ?? '';

                        return [
                            'id' => $album->id,
                            'name' => $name,
                            'show_album' => (bool) $album->show_album,
                            'cover_image' => $album->cover_image_url,
                            'items_count' => $album->items->where('is_active', true)->count(),
                        ];
                    });

                return $this->responseService->success($data, __('gallery.albums_loaded'));
            }

            // Otherwise, return items from albums directly.
            $items = $albums
                ->where('show_album', false)
                ->flatMap(function ($album) use ($locale) {
                    return $album->items
                        ->where('is_active', true)
                        ->sortBy('sort_order')
                        ->values()
                        ->map(function ($item) use ($album, $locale) {
                            $translation = $item->translations->firstWhere('locale', $locale)
                                ?? $item->translations->first();

                            return [
                                'id' => $item->id,
                                'album_id' => $album->id,
                                'album_name' => $album->translations->firstWhere('locale', $locale)?->name
                                    ?? $album->translations->first()?->name
                                    ?? '',
                                'title' => $translation?->title ?? '',
                                'description' => $translation?->description ?? '',
                                'type' => $item->type,
                                'file_url' => $item->file_url,
                                'video_url' => $item->video_url,
                                'publication' => (bool) $item->publication,
                                'sort_order' => (int) $item->sort_order,
                            ];
                        });
                })
                ->values();

            return $this->responseService->success($items, __('gallery.items_loaded'));
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * GET /api/v1/gallery/{menuId}/albums/{albumId}
     * Album details (meta) + items
     */
    public function showAlbum(Request $request, $menuId, $albumId): JsonResponse
    {
        try {
            $menuIdInt = (int) $menuId;
            $albumIdInt = (int) $albumId;

            $album = GalleryAlbum::query()
                ->with(['translations', 'items.translations'])
                ->where('id', $albumIdInt)
                ->where('menu_id', $menuIdInt)
                ->where('is_active', true)
                ->first();

            if (!$album) {
                return $this->responseService->error(__('gallery.album_not_found'), 404);
            }

            $locale = app()->getLocale();

            $albumName = $album->translations->firstWhere('locale', $locale)?->name
                ?? $album->translations->first()?->name
                ?? '';

            $items = $album->items
                ->where('is_active', true)
                ->sortBy('sort_order')
                ->values()
                ->map(function ($item) use ($locale) {
                    $translation = $item->translations->firstWhere('locale', $locale)
                        ?? $item->translations->first();

                    return [
                        'id' => $item->id,
                        'title' => $translation?->title ?? '',
                        'description' => $translation?->description ?? '',
                        'type' => $item->type,
                        'file_url' => $item->file_url,
                        'video_url' => $item->video_url,
                        'publication' => (bool) $item->publication,
                        'sort_order' => (int) $item->sort_order,
                    ];
                });

            $response = [
                'album' => [
                    'id' => $album->id,
                    'name' => $albumName,
                    'show_album' => (bool) $album->show_album,
                    'cover_image' => $album->cover_image_url,
                ],
                'items' => $items,
            ];

            return $this->responseService->success($response, __('gallery.album_loaded'));
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * GET /api/v1/gallery/{menuId}/items
     * Items only (flattened) for menu (useful when show_album=0)
     */
    public function itemsByMenu(Request $request, $menuId): JsonResponse
    {
        try {
            $menuIdInt = (int) $menuId;

            $albums = GalleryAlbum::query()
                ->with(['translations', 'items.translations'])
                ->where('menu_id', $menuIdInt)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            $locale = app()->getLocale();

            $items = $albums
                ->flatMap(function ($album) use ($locale) {
                    return $album->items
                        ->where('is_active', true)
                        ->sortBy('sort_order')
                        ->values()
                        ->map(function ($item) use ($album, $locale) {
                            $translation = $item->translations->firstWhere('locale', $locale)
                                ?? $item->translations->first();

                            return [
                                'id' => $item->id,
                                'album_id' => $album->id,
                                'title' => $translation?->title ?? '',
                                'description' => $translation?->description ?? '',
                                'type' => $item->type,
                                'file_url' => $item->file_url,
                                'video_url' => $item->video_url,
                                'publication' => (bool) $item->publication,
                                'sort_order' => (int) $item->sort_order,
                            ];
                        });
                })
                ->values();

            return $this->responseService->success($items, __('gallery.items_loaded'));
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * GET /api/v1/gallery/{menuId}/albums/{albumId}/items
     * Items only for a specific album
     */
    public function itemsByAlbum(Request $request, $menuId, $albumId): JsonResponse
    {
        try {
            $menuIdInt = (int) $menuId;
            $albumIdInt = (int) $albumId;

            $album = GalleryAlbum::query()
                ->with(['items.translations'])
                ->where('id', $albumIdInt)
                ->where('menu_id', $menuIdInt)
                ->where('is_active', true)
                ->first();

            if (!$album) {
                return $this->responseService->error(__('gallery.album_not_found'), 404);
            }

            $locale = app()->getLocale();

            $items = $album->items
                ->where('is_active', true)
                ->sortBy('sort_order')
                ->values()
                ->map(function ($item) use ($locale) {
                    $translation = $item->translations->firstWhere('locale', $locale)
                        ?? $item->translations->first();

                    return [
                        'id' => $item->id,
                        'title' => $translation?->title ?? '',
                        'description' => $translation?->description ?? '',
                        'type' => $item->type,
                        'file_url' => $item->file_url,
                        'video_url' => $item->video_url,
                        'publication' => (bool) $item->publication,
                        'sort_order' => (int) $item->sort_order,
                    ];
                });

            return $this->responseService->success($items, __('gallery.items_loaded'));
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }
}
