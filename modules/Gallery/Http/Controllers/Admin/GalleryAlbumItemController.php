<?php

namespace Modules\Gallery\Http\Controllers\Admin;

use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Services\Upload\FileUploadService;
use App\Services\Upload\ImageUploadService;
use App\Services\Upload\VideoUploadService;
use App\Support\Settings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Gallery\Http\Requests\StoreAlbumItemRequest;
use Modules\Gallery\Http\Requests\UpdateAlbumItemRequest;
use Modules\Gallery\Models\GalleryAlbum;
use Modules\Gallery\Models\GalleryAlbumItem;
use Modules\Menu\Models\Menu;

class GalleryAlbumItemController extends Controller
{
    public function __construct(
        private readonly ImageUploadService $imageUploadService,
        private readonly VideoUploadService $videoUploadService,
        private readonly FileUploadService $fileUploadService
    ) {
    }

    public function index(Menu $menu, GalleryAlbum $album): View
    {
        $this->ensureAlbumBelongsToMenu($menu, $album);

        $items = GalleryAlbumItem::query()
            ->with('translations')
            ->where('gallery_album_id', $album->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $menuType = $this->resolveMenuType($menu);

        return view('gallery::admin.items.index', compact('menu', 'album', 'items', 'menuType'));
    }

    public function create(Menu $menu, GalleryAlbum $album): View
    {
        $this->ensureAlbumBelongsToMenu($menu, $album);

        return view('gallery::admin.items.create', $this->buildFormViewData($menu, $album));
    }

    public function store(StoreAlbumItemRequest $request, Menu $menu, GalleryAlbum $album): RedirectResponse
    {
        $this->ensureAlbumBelongsToMenu($menu, $album);

        try {
            DB::transaction(function () use ($request, $menu, $album): void {
                $menuType = $this->resolveMenuType($menu);

                $data = $request->validated();
                $data['gallery_album_id'] = $album->id;
                $data['type'] = $this->resolveItemType($menuType);
                $data['publication'] = $menuType === 'files' ? $request->boolean('publication') : false;
                $data['is_active'] = $request->boolean('is_active');
                $data['video_url'] = null;
                $data['sort_order'] = ((int) GalleryAlbumItem::query()
                        ->where('gallery_album_id', $album->id)
                        ->max('sort_order')) + 1;

                if ($request->hasFile('file')) {
                    $data['file_path'] = $this->uploadByMenuType(
                        $menuType,
                        $request->file('file')
                    );
                }

                $item = GalleryAlbumItem::query()->create($data);

                $this->syncTranslations(
                    $item,
                    (array) $request->input('title', []),
                    (array) $request->input('description', [])
                );
            });

            return redirect()
                ->route('admin.gallery.items.index', [$menu, $album])
                ->with('success', __('Item created successfully.'));
        } catch (\Throwable $throwable) {
            return back()
                ->withInput()
                ->with('error', __('An unexpected error occurred while creating the item.'));
        }
    }

    public function edit(Menu $menu, GalleryAlbum $album, GalleryAlbumItem $item): View
    {
        $this->ensureAlbumBelongsToMenu($menu, $album);
        $this->ensureItemBelongsToAlbum($album, $item);

        return view('gallery::admin.items.edit', $this->buildFormViewData($menu, $album, $item));
    }

    public function update(UpdateAlbumItemRequest $request, Menu $menu, GalleryAlbum $album, GalleryAlbumItem $item): RedirectResponse
    {
        $this->ensureAlbumBelongsToMenu($menu, $album);
        $this->ensureItemBelongsToAlbum($album, $item);

        try {
            DB::transaction(function () use ($request, $menu, $item): void {
                $menuType = $this->resolveMenuType($menu);

                $data = $request->validated();
                $data['type'] = $this->resolveItemType($menuType);
                $data['publication'] = $menuType === 'files' ? $request->boolean('publication') : false;
                $data['is_active'] = $request->boolean('is_active');
                $data['video_url'] = null;

                if ($request->hasFile('file')) {
                    $this->deleteStoredFile($item->file_path);

                    $data['file_path'] = $this->uploadByMenuType(
                        $menuType,
                        $request->file('file')
                    );
                }

                $item->update($data);

                $item->translations()->delete();

                $this->syncTranslations(
                    $item,
                    (array) $request->input('title', []),
                    (array) $request->input('description', [])
                );
            });

            return redirect()
                ->route('admin.gallery.items.index', [$menu, $album])
                ->with('success', __('Item updated successfully.'));
        } catch (\Throwable $throwable) {
            return back()
                ->withInput()
                ->with('error', __('An unexpected error occurred while updating the item.'));
        }
    }

    public function destroy(Menu $menu, GalleryAlbum $album, GalleryAlbumItem $item): JsonResponse
    {
        $this->ensureAlbumBelongsToMenu($menu, $album);
        $this->ensureItemBelongsToAlbum($album, $item);

        try {
            DB::transaction(function () use ($item): void {
                $this->deleteStoredFile($item->file_path);
                $item->delete();
            });

            return response()->json([
                'success' => true,
                'message' => __('Item deleted successfully.'),
            ]);
        } catch (\Throwable $throwable) {
            return response()->json([
                'success' => false,
                'message' => __('An unexpected error occurred while deleting the item.'),
            ], 500);
        }
    }

    public function updateOrder(Request $request, Menu $menu, GalleryAlbum $album): JsonResponse
    {
        $this->ensureAlbumBelongsToMenu($menu, $album);

        $order = $request->input('order', []);

        if (! is_array($order)) {
            return response()->json([
                'success' => false,
                'message' => __('Invalid sort payload.'),
            ], 422);
        }

        try {
            DB::transaction(function () use ($order, $album): void {
                foreach (array_values($order) as $index => $id) {
                    GalleryAlbumItem::query()
                        ->where('id', (int) $id)
                        ->where('gallery_album_id', $album->id)
                        ->update([
                            'sort_order' => $index + 1,
                        ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => __('Item order updated successfully.'),
            ]);
        } catch (\Throwable $throwable) {
            return response()->json([
                'success' => false,
                'message' => __('An unexpected error occurred while updating item order.'),
            ], 500);
        }
    }

    private function buildFormViewData(Menu $menu, GalleryAlbum $album, ?GalleryAlbumItem $item = null): array
    {
        $languages = Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->orderByDesc('is_default_admin')
            ->orderBy('sort_order')
            ->get();

        $requiredLocales = Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->where('is_required', true)
            ->pluck('code')
            ->all();

        $translationData = [];

        if ($item) {
            $item->loadMissing('translations');

            foreach ($item->translations as $translation) {
                $translationData[$translation->locale] = [
                    'title' => $translation->title,
                    'description' => $translation->description,
                ];
            }
        }

        $menuType = $this->resolveMenuType($menu);

        return [
            'menu' => $menu,
            'album' => $album,
            'item' => $item,
            'languages' => $languages,
            'requiredLocales' => $requiredLocales,
            'translationData' => $translationData,
            'menuType' => $menuType,
            'uploadMeta' => $this->buildUploadMeta($menuType),
        ];
    }

    private function buildUploadMeta(string $menuType): array
    {
        $allowedImages = $this->settingsList('allowed_images', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
        $allowedVideos = $this->settingsList('allowed_videos', ['mp4', 'avi']);
        $allowedFiles = $this->settingsList('allowed_files', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);

        $maxImageSize = (string) Settings::get('file_manager', 'max_image_size', '10MB');
        $maxVideoSize = (string) Settings::get('file_manager', 'max_video_size', '20MB');
        $maxFileSize = (string) Settings::get('file_manager', 'max_file_size', '10MB');

        return match ($menuType) {
            'photo_gallery' => [
                'section_title' => __('Photo upload'),
                'file_label' => __('Photo'),
                'file_accept' => $this->buildAcceptAttribute($allowedImages),
                'allowed_label' => strtoupper(implode(', ', $allowedImages)),
                'max_label' => $maxImageSize,
                'help_text' => __('Allowed formats: :formats. Maximum size: :size.', [
                    'formats' => strtoupper(implode(', ', $allowedImages)),
                    'size' => $maxImageSize,
                ]),
            ],
            'video_gallery' => [
                'section_title' => __('Video upload'),
                'file_label' => __('Video'),
                'file_accept' => $this->buildAcceptAttribute($allowedVideos),
                'allowed_label' => strtoupper(implode(', ', $allowedVideos)),
                'max_label' => $maxVideoSize,
                'help_text' => __('Allowed formats: :formats. Maximum size: :size.', [
                    'formats' => strtoupper(implode(', ', $allowedVideos)),
                    'size' => $maxVideoSize,
                ]),
            ],
            default => [
                'section_title' => __('File upload'),
                'file_label' => __('File'),
                'file_accept' => $this->buildAcceptAttribute($allowedFiles),
                'allowed_label' => strtoupper(implode(', ', $allowedFiles)),
                'max_label' => $maxFileSize,
                'help_text' => __('Allowed formats: :formats. Maximum size: :size.', [
                    'formats' => strtoupper(implode(', ', $allowedFiles)),
                    'size' => $maxFileSize,
                ]),
            ],
        };
    }

    private function buildAcceptAttribute(array $extensions): string
    {
        return collect($extensions)
            ->map(static fn (string $extension): string => '.' . ltrim(strtolower($extension), '.'))
            ->implode(',');
    }

    private function settingsList(string $key, array $default): array
    {
        $value = Settings::get('file_manager', $key, $default);

        if (is_string($value)) {
            $value = explode(',', $value);
        }

        return collect((array) $value)
            ->map(static fn ($item): string => strtolower(trim((string) $item)))
            ->filter()
            ->values()
            ->all();
    }

    private function resolveMenuType(Menu $menu): string
    {
        return $menu->type instanceof \BackedEnum
            ? $menu->type->value
            : (string) $menu->type;
    }

    private function resolveItemType(string $menuType): string
    {
        return match ($menuType) {
            'photo_gallery' => 'photo',
            'video_gallery' => 'video',
            'files' => 'file',
            default => 'photo',
        };
    }

    private function uploadByMenuType(string $menuType, $file): string
    {
        return match ($menuType) {
            'photo_gallery' => $this->imageUploadService->uploadImage(
                $file,
                'gallery/items/photos',
                'file'
            ),
            'video_gallery' => $this->videoUploadService->uploadVideo(
                $file,
                'gallery/items/videos',
                'file'
            ),
            'files' => $this->fileUploadService->uploadFile(
                $file,
                'gallery/items/files',
                'file'
            ),
            default => $this->imageUploadService->uploadImage(
                $file,
                'gallery/items/photos',
                'file'
            ),
        };
    }

    private function syncTranslations(GalleryAlbumItem $item, array $titles, array $descriptions): void
    {
        $locales = collect(array_keys($titles))
            ->merge(array_keys($descriptions))
            ->unique()
            ->values();

        foreach ($locales as $locale) {
            $title = trim((string) ($titles[$locale] ?? ''));
            $description = trim((string) ($descriptions[$locale] ?? ''));

            if ($title === '' && $description === '') {
                continue;
            }

            $item->translations()->create([
                'locale' => (string) $locale,
                'title' => $title,
                'description' => $description,
            ]);
        }
    }

    private function deleteStoredFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function ensureAlbumBelongsToMenu(Menu $menu, GalleryAlbum $album): void
    {
        abort_if((int) $album->menu_id !== (int) $menu->id, 404);
    }

    private function ensureItemBelongsToAlbum(GalleryAlbum $album, GalleryAlbumItem $item): void
    {
        abort_if((int) $item->gallery_album_id !== (int) $album->id, 404);
    }
}
