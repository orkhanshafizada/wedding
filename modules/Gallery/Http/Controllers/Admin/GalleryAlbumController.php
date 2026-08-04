<?php

namespace Modules\Gallery\Http\Controllers\Admin;

use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Services\Upload\ImageUploadService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Gallery\Http\Requests\StoreAlbumRequest;
use Modules\Gallery\Http\Requests\UpdateAlbumRequest;
use Modules\Gallery\Models\GalleryAlbum;
use Modules\Menu\Models\Menu;

class GalleryAlbumController extends Controller
{
    public function __construct(
        private readonly ImageUploadService $imageUploadService
    ) {
    }

    public function index(Menu $menu): View
    {
        $albums = GalleryAlbum::with('translations', 'items')
            ->where('menu_id', $menu->id)
            ->orderBy('sort_order')
            ->get();

        return view('gallery::admin.albums.index', compact('menu', 'albums'));
    }

    public function create(Menu $menu): View
    {
        $languages = Language::where('status',  StatusEnum::ACTIVE)
            ->orderByDesc('is_default_admin')
            ->orderBy('sort_order')
            ->get();

        $requiredLocales = Language::where('is_required', true)
            ->pluck('code')
            ->toArray();

        return view('gallery::admin.albums.create', compact('menu', 'languages', 'requiredLocales'));
    }

    public function store(StoreAlbumRequest $request, Menu $menu): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['menu_id'] = $menu->id;
            $data['sort_order'] = ((int) GalleryAlbum::where('menu_id', $menu->id)->max('sort_order')) + 1;
            $data['show_album'] = $request->boolean('show_album');
            $data['is_active'] = $request->boolean('is_active');

            // Handle cover image upload
            if ($request->hasFile('cover_image')) {
                $data['cover_image'] = $this->imageUploadService->uploadImage(
                    $request->file('cover_image'),
                    'gallery/albums'
                );
            }

            $album = GalleryAlbum::create($data);

            // Save translations
            foreach ($request->input('name', []) as $locale => $name) {
                if (!empty($name)) {
                    $album->translations()->create([
                        'locale' => $locale,
                        'name' => $name,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.gallery.index', $menu)
                ->with('success', __('Album created successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', __('An error occurred'))->withInput();
        }
    }

    public function edit(Menu $menu, GalleryAlbum $album): View
    {
        $languages = Language::where('status',  StatusEnum::ACTIVE)
            ->orderByDesc('is_default_admin')
            ->orderBy('sort_order')
            ->get();

        $requiredLocales = Language::where('is_required', true)
            ->pluck('code')
            ->toArray();

        $album->load('translations');

        $translationData = [];
        foreach ($album->translations as $translation) {
            $translationData[$translation->locale] = [
                'name' => $translation->name,
            ];
        }

        return view('gallery::admin.albums.edit', compact('menu', 'album', 'languages', 'requiredLocales', 'translationData'));
    }

    public function update(UpdateAlbumRequest $request, Menu $menu, GalleryAlbum $album): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['show_album'] = $request->boolean('show_album');
            $data['is_active'] = $request->boolean('is_active');

            // Handle cover image upload
            if ($request->hasFile('cover_image')) {
                // Delete old image
                if ($album->cover_image) {
                    Storage::disk('public')->delete($album->cover_image);
                }

                $data['cover_image'] = $this->imageUploadService->uploadImage(
                    $request->file('cover_image'),
                    'gallery/albums'
                );
            }

            $album->update($data);

            // Update translations
            $album->translations()->delete();
            foreach ($request->input('name', []) as $locale => $name) {
                if (!empty($name)) {
                    $album->translations()->create([
                        'locale' => $locale,
                        'name' => $name,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.gallery.index', $menu)
                ->with('success', __('Album updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', __('An error occurred'))->withInput();
        }
    }

    public function destroy(Menu $menu, GalleryAlbum $album): JsonResponse
    {
        try {
            // Delete cover image
            if ($album->cover_image) {
                Storage::disk('public')->delete($album->cover_image);
            }

            $album->delete();

            return response()->json([
                'success' => true,
                'message' => __('Album deleted successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('An error occurred'),
            ], 500);
        }
    }

    public function updateOrder(Request $request, Menu $menu): JsonResponse
    {
        try {
            $order = $request->input('order', []);

            foreach ($order as $index => $id) {
                GalleryAlbum::where('id', $id)
                    ->where('menu_id', $menu->id)
                    ->update(['sort_order' => $index]);
            }

            return response()->json([
                'success' => true,
                'message' => __('Order updated successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('An error occurred'),
            ], 500);
        }
    }
}
