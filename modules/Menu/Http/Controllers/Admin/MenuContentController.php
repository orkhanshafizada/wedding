<?php

namespace Modules\Menu\Http\Controllers\Admin;

use App\Enums\StatusEnum;
use App\Models\Language;
use App\Services\Upload\FileUploadService;
use App\Services\Upload\ImageUploadService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Menu\Http\Requests\Admin\UpdateMenuContentRequest;
use Modules\Menu\Models\Menu;
use Modules\Menu\Models\MenuContent;
use Modules\Menu\Models\MenuContentFile;

class MenuContentController extends Controller
{
    private ImageUploadService $imageUploadService;
    private FileUploadService $fileUploadService;

    public function __construct(ImageUploadService $imageUploadService, FileUploadService $fileUploadService)
    {
        $this->imageUploadService = $imageUploadService;
        $this->fileUploadService = $fileUploadService;
    }

    public function edit(Menu $menu): View
    {
        $page = MenuContent::firstOrCreate(['menu_id' => $menu->id], ['data' => []]);

        $languages = Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->orderBy('sort_order')
            ->get(['code', 'name', 'is_required']);

        $locales = $languages->pluck('code')->filter()->values()->all();
        if ($locales === []) {
            $locales = [config('app.locale')];
        }

        $requiredLocales = $languages
            ->where('is_required', 1)
            ->pluck('code')
            ->filter()
            ->values()
            ->all();

        if ($requiredLocales === []) {
            $requiredLocales = [$locales[0]];
        } else {
            $requiredLocales = array_values(array_intersect($requiredLocales, $locales));
            if ($requiredLocales === []) {
                $requiredLocales = [$locales[0]];
            }
        }

        $active = $locales[0];

        $data = Arr::get($page->data ?? [], $active, [
            'title' => '',
            'description' => '',
        ]);

        $files = $page->files()->orderBy('sort_order')->orderBy('id')->get();

        return view('menu::admin.menu.content.edit', compact(
            'menu',
            'page',
            'languages',
            'locales',
            'requiredLocales',
            'active',
            'data',
            'files'
        ));
    }

    public function update(UpdateMenuContentRequest $request, Menu $menu): RedirectResponse
    {
        $page = MenuContent::firstOrCreate(['menu_id' => $menu->id], ['data' => []]);

        $locales = Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->values()
            ->all();

        if ($locales === []) {
            $locales = [config('app.locale')];
        }

        $required = Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->where('is_required', 1)
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->values()
            ->all();

        if ($required === []) {
            $required = [$locales[0]];
        } else {
            $required = array_values(array_intersect($required, $locales));
            if ($required === []) {
                $required = [$locales[0]];
            }
        }

        $titles = (array) $request->input('title', []);
        $descs  = (array) $request->input('description', []);

        $payload = is_array($page->data) ? $page->data : [];

        foreach ($locales as $code) {
            $title = trim((string) ($titles[$code] ?? ''));
            $desc  = trim((string) ($descs[$code] ?? ''));

            if (in_array($code, $required, true)) {
                $payload[$code] = ['title' => $title, 'description' => $desc];
                continue;
            }

            if ($title === '' && $desc === '') {
                unset($payload[$code]);
            } else {
                $payload[$code] = ['title' => $title, 'description' => $desc];
            }
        }

        DB::transaction(function () use ($request, $menu, $page, $payload): void {
            if ($request->hasFile('main_photo')) {
                if (!empty($page->main_photo)) {
                    Storage::disk('public')->delete($page->main_photo);
                }

                $dir = 'menu/content/' . $menu->id;
                $page->main_photo = $this->imageUploadService->uploadImage(
                    $request->file('main_photo'),
                    $dir,
                    'main_photo'
                );
            }

            $page->data = $payload;
            $page->save();

            $uploads = (array) $request->file('files', []);
            $uploads = array_values(array_filter(
                $uploads,
                fn ($f) => $f instanceof \Illuminate\Http\UploadedFile
            ));

            if ($uploads !== []) {
                $dir = 'menu/content/' . $menu->id . '/files';

                $maxSort = (int) $page->files()->max('sort_order');
                $nextSort = $maxSort + 1;

                foreach ($uploads as $idx => $file) {
                    $originalName = (string) $file->getClientOriginalName();
                    $ext = strtolower((string) ($file->getClientOriginalExtension() ?: 'bin'));

                    $field = 'files.' . $idx;

                    $path = $this->isAudioExtension($ext)
                        ? $this->fileUploadService->uploadAudio($file, $dir, $field)
                        : $this->fileUploadService->uploadFile($file, $dir, $field);

                    $page->files()->create([
                        'path' => $path,
                        'original_name' => $originalName,
                        'extension' => $ext !== '' ? $ext : 'bin',
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize() ? (int) $file->getSize() : null,
                        'sort_order' => $nextSort,
                    ]);

                    $nextSort++;
                }
            }
        });

        return redirect()
            ->route('admin.menus.content.edit', $menu)
            ->with('success', __('Saved successfully.'));
    }

    public function uploadFiles(Request $request, Menu $menu): JsonResponse
    {
        $page = MenuContent::firstOrCreate(['menu_id' => $menu->id], ['data' => []]);

        $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file'],
        ], [
            'files.required' => __('Please select at least one file.'),
            'files.array' => __('Invalid files payload.'),
            'files.min' => __('Please select at least one file.'),
            'files.*.file' => __('Invalid file uploaded.'),
        ]);

        $uploads = (array) $request->file('files', []);
        $uploads = array_values(array_filter(
            $uploads,
            fn ($f) => $f instanceof \Illuminate\Http\UploadedFile
        ));

        if ($uploads === []) {
            return response()->json([
                'ok' => false,
                'message' => __('Please select at least one file.'),
            ], 422);
        }

        $created = [];
        $skipped = [];

        DB::transaction(function () use ($page, $menu, $uploads, &$created, &$skipped): void {
            $dir = 'menu/content/' . $menu->id . '/files';

            $maxSort = (int) $page->files()->max('sort_order');
            $nextSort = $maxSort + 1;

            foreach ($uploads as $idx => $file) {
                $originalName = (string) $file->getClientOriginalName();
                $ext = strtolower((string) ($file->getClientOriginalExtension() ?: ''));

                $field = 'files.' . $idx;

                try {
                    if ($this->looksLikeImage($file, $ext)) {
                        $path = $this->imageUploadService->uploadImage($file, $dir, $field);

                        $savedExt = strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg');

                        $row = $page->files()->create([
                            'path' => $path,
                            'original_name' => $originalName,
                            'extension' => $savedExt,
                            'mime_type' => $file->getClientMimeType(),
                            'size' => $file->getSize() ? (int) $file->getSize() : null,
                            'sort_order' => $nextSort,
                        ]);

                        $created[] = [
                            'id' => (int) $row->id,
                            'original_name' => (string) $row->original_name,
                            'extension' => (string) $row->extension,
                            'size' => $row->size ? (int) $row->size : null,
                            'url' => asset('storage/' . ltrim($row->path, '/')),
                            'delete_url' => route('admin.menus.content.files.delete', ['menu' => $menu->id, 'file' => $row->id]),
                        ];

                        $nextSort++;
                        continue;
                    }

                    if ($this->isAudioExtension($ext)) {
                        $path = $this->fileUploadService->uploadAudio($file, $dir, $field);
                    } else {
                        $path = $this->fileUploadService->uploadFile($file, $dir, $field);
                    }

                    $row = $page->files()->create([
                        'path' => $path,
                        'original_name' => $originalName,
                        'extension' => $ext !== '' ? $ext : 'bin',
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize() ? (int) $file->getSize() : null,
                        'sort_order' => $nextSort,
                    ]);

                    $created[] = [
                        'id' => (int) $row->id,
                        'original_name' => (string) $row->original_name,
                        'extension' => (string) $row->extension,
                        'size' => $row->size ? (int) $row->size : null,
                        'delete_url' => route('admin.menus.content.files.delete', ['menu' => $menu->id, 'file' => $row->id]),
                    ];

                    $nextSort++;
                } catch (\Illuminate\Validation\ValidationException $e) {
                    $skipped[] = [
                        'name' => $originalName,
                        'reason' => $this->friendlyUploadError($originalName, $ext, $e),
                    ];
                } catch (\Throwable $e) {
                    $skipped[] = [
                        'name' => $originalName,
                        'reason' => __('Failed to upload ":name".', ['name' => $originalName]),
                    ];
                }
            }
        });

        if ($created === []) {
            $first = $skipped[0]['reason'] ?? __('No files were uploaded.');
            return response()->json([
                'ok' => false,
                'message' => $first,
                'skipped' => $skipped,
            ], 422);
        }

        $msg = __('Uploaded :count file(s).', ['count' => count($created)]);
        if ($skipped !== []) {
            $msg .= ' ' . __('Skipped :count file(s).', ['count' => count($skipped)]);
        }

        return response()->json([
            'ok' => true,
            'message' => $msg,
            'files' => $created,
            'skipped' => $skipped,
        ]);
    }

    private function looksLikeImage(\Illuminate\Http\UploadedFile $file, string $ext): bool
    {
        $mime = strtolower((string) ($file->getClientMimeType() ?: ''));
        if ($mime !== '' && str_starts_with($mime, 'image/')) {
            return true;
        }

        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'tiff'], true);
    }

    private function friendlyUploadError(string $originalName, string $ext, \Illuminate\Validation\ValidationException $e): string
    {
        $flat = [];
        foreach ($e->errors() as $msgs) {
            foreach ((array) $msgs as $m) {
                $flat[] = (string) $m;
            }
        }
        $raw = $flat[0] ?? '';

        if ($raw !== '' && str_contains(mb_strtolower($raw), 'invalid')) {
            $label = $ext !== '' ? strtoupper($ext) : __('This');
            return __(':ext file extension is not supported: :name', [
                'ext' => $label,
                'name' => $originalName,
            ]);
        }

        if ($raw !== '') {
            return __(':msg (:name)', [
                'msg' => $raw,
                'name' => $originalName,
            ]);
        }

        $label = $ext !== '' ? strtoupper($ext) : __('This');
        return __(':ext file extension is not supported: :name', [
            'ext' => $label,
            'name' => $originalName,
        ]);
    }

    public function deleteFile(Menu $menu, MenuContentFile $file): JsonResponse
    {
        $page = MenuContent::where('menu_id', $menu->id)->first();

        if (!$page || (int) $file->menu_content_id !== (int) $page->id) {
            return response()->json([
                'ok' => false,
                'message' => __('Invalid file.'),
            ], 422);
        }

        DB::transaction(function () use ($file): void {
            if (!empty($file->path)) {
                Storage::disk('public')->delete($file->path);
            }

            $file->delete();
        });

        return response()->json([
            'ok' => true,
            'message' => __('File deleted.'),
        ]);
    }

    public function reorderFiles(Request $request, Menu $menu): JsonResponse
    {
        $page = MenuContent::where('menu_id', $menu->id)->first();

        if (!$page) {
            return response()->json([
                'ok' => false,
                'message' => __('Content page not found.'),
            ], 404);
        }

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $ids = array_values(array_unique(array_map('intval', (array) $data['ids'])));

        $existsCount = MenuContentFile::query()
            ->where('menu_content_id', $page->id)
            ->whereIn('id', $ids)
            ->count();

        if ($existsCount !== count($ids)) {
            return response()->json([
                'ok' => false,
                'message' => __('Invalid files order.'),
            ], 422);
        }

        DB::transaction(function () use ($page, $ids): void {
            foreach ($ids as $i => $id) {
                MenuContentFile::query()
                    ->where('menu_content_id', $page->id)
                    ->where('id', $id)
                    ->update(['sort_order' => $i + 1]);
            }
        });

        return response()->json([
            'ok' => true,
            'message' => __('Order updated.'),
        ]);
    }

    private function isAudioExtension(string $ext): bool
    {
        $ext = strtolower(trim($ext));

        return in_array($ext, [
            'mp3', 'wav', 'ogg', 'aac', 'm4a', 'flac',
        ], true);
    }
}
