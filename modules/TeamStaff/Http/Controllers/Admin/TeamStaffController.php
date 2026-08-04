<?php

namespace Modules\TeamStaff\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Upload\FileUploadService;
use App\Services\Upload\ImageUploadService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Menu\Models\Menu;
use Modules\TeamStaff\Http\Requests\Admin\StoreTeamStaffRequest;
use Modules\TeamStaff\Http\Requests\Admin\UpdateTeamStaffRequest;
use Modules\TeamStaff\Models\TeamStaff;

class TeamStaffController extends Controller
{
    public function __construct(
        private readonly ImageUploadService $imageUploadService,
        private readonly FileUploadService $fileUploadService
    ) {
    }

    public function index(Menu $menu): View
    {
        $teamStaff = TeamStaff::query()
            ->where('menu_id', $menu->id)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(20);

        $adminLang = app()->getLocale();

        return view('teamstaff::admin.team-staff.index', compact('teamStaff', 'adminLang', 'menu'));
    }

    public function create(Menu $menu): View
    {
        return view('teamstaff::admin.team-staff.create', compact('menu'));
    }

    public function store(StoreTeamStaffRequest $request, Menu $menu): RedirectResponse
    {
        $data = $request->validated();

        $teamStaff = DB::transaction(function () use ($data, $request, $menu): TeamStaff {
            $profilePicturePath = $request->hasFile('profile_picture')
                ? $this->uploadProfilePicture($request->file('profile_picture'))
                : null;

            $filePaths = $request->hasFile('files')
                ? $this->uploadFiles((array) $request->file('files'))
                : [];

            return TeamStaff::query()->create([
                'menu_id' => $menu->id,
                'name' => Arr::get($data, 'name', []),
                'company' => Arr::get($data, 'company', []),
                'position' => Arr::get($data, 'position', []),
                'description' => Arr::get($data, 'description', []),
                'color' => Arr::get($data, 'color'),
                'phone' => Arr::get($data, 'phone'),
                'mobile' => Arr::get($data, 'mobile'),
                'email' => Arr::get($data, 'email'),
                'profile_picture' => $profilePicturePath,
                'social_networks' => Arr::get($data, 'social_networks', []),
                'files' => $filePaths,
                'is_active' => (bool) Arr::get($data, 'is_active', false),
                'sort_order' => $this->nextSortOrder($menu),
            ]);
        });

        return redirect()
            ->route('admin.team-staff.index', $menu)
            ->with('success', __('Team staff member created successfully.'));
    }

    public function edit(Menu $menu, TeamStaff $teamStaff): View
    {
        $this->assertBelongsToMenu($teamStaff, $menu);

        return view('teamstaff::admin.team-staff.edit', compact('teamStaff', 'menu'));
    }

    public function update(UpdateTeamStaffRequest $request, Menu $menu, TeamStaff $teamStaff): RedirectResponse
    {
        $this->assertBelongsToMenu($teamStaff, $menu);

        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $teamStaff): void {
            $existingFileResult = $this->normalizeExistingFiles(
                (array) Arr::get($data, 'existing_files', []),
                (array) ($teamStaff->files ?? [])
            );

            $filePaths = $existingFileResult['kept'];

            if ($request->hasFile('files')) {
                $filePaths = array_merge($filePaths, $this->uploadFiles((array) $request->file('files')));
            }

            $updateData = [
                'name' => Arr::get($data, 'name', []),
                'company' => Arr::get($data, 'company', []),
                'position' => Arr::get($data, 'position', []),
                'description' => Arr::get($data, 'description', []),
                'color' => Arr::get($data, 'color'),
                'phone' => Arr::get($data, 'phone'),
                'mobile' => Arr::get($data, 'mobile'),
                'email' => Arr::get($data, 'email'),
                'social_networks' => Arr::get($data, 'social_networks', []),
                'files' => array_values($filePaths),
                'is_active' => (bool) Arr::get($data, 'is_active', false),
            ];

            if ($request->hasFile('profile_picture')) {
                $oldProfilePicture = (string) ($teamStaff->profile_picture ?? '');

                $updateData['profile_picture'] = $this->uploadProfilePicture($request->file('profile_picture'));

                $this->deleteFile($oldProfilePicture);
            }

            $teamStaff->update($updateData);

            $this->deleteFiles($existingFileResult['deleted']);
        });

        return redirect()
            ->route('admin.team-staff.index', $menu)
            ->with('success', __('Team staff member updated successfully.'));
    }

    public function destroy(Menu $menu, TeamStaff $teamStaff): RedirectResponse
    {
        $this->assertBelongsToMenu($teamStaff, $menu);

        DB::transaction(function () use ($teamStaff): void {
            $profilePicture = (string) ($teamStaff->profile_picture ?? '');
            $files = (array) ($teamStaff->files ?? []);

            $teamStaff->delete();

            $this->deleteFile($profilePicture);
            $this->deleteFiles($files);
        });

        return redirect()
            ->route('admin.team-staff.index', $menu)
            ->with('success', __('Team staff member deleted successfully.'));
    }

    public function updateOrder(StoreTeamStaffRequest $request, Menu $menu): JsonResponse
    {
        $data = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*.id' => ['required', 'integer', 'exists:team_staff,id'],
            'order.*.position' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($data, $menu): void {
            foreach ($data['order'] as $item) {
                TeamStaff::query()
                    ->where('id', (int) $item['id'])
                    ->where('menu_id', $menu->id)
                    ->update([
                        'sort_order' => (int) $item['position'],
                    ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => __('Order updated successfully.'),
        ]);
    }

    private function assertBelongsToMenu(TeamStaff $teamStaff, Menu $menu): void
    {
        abort_unless((int) $teamStaff->menu_id === (int) $menu->id, 404);
    }

    private function nextSortOrder(Menu $menu): int
    {
        return ((int) TeamStaff::query()
                ->where('menu_id', $menu->id)
                ->max('sort_order')) + 1;
    }

    private function uploadProfilePicture(mixed $file): string
    {
        return $this->imageUploadService->uploadImage(
            $file,
            'team-staff/profiles',
            'profile_picture'
        );
    }

    private function uploadFiles(array $files): array
    {
        $paths = [];

        foreach ($files as $index => $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }

            $paths[] = $this->uploadFile($file, $index);
        }

        return $paths;
    }

    private function uploadFile(mixed $file, int $index): string
    {
        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: ''));

        if ($this->isImageExtension($extension)) {
            return $this->imageUploadService->uploadImage(
                $file,
                'team-staff/files',
                'files.' . $index
            );
        }

        return $this->fileUploadService->uploadFile(
            $file,
            'team-staff/files',
            'files.' . $index
        );
    }

    private function normalizeExistingFiles(array $rows, array $currentFiles): array
    {
        $currentFileMap = collect($currentFiles)
            ->filter(fn ($path): bool => is_string($path) && trim($path) !== '')
            ->mapWithKeys(fn (string $path): array => [$path => true]);

        $keptRows = [];
        $submittedPaths = [];

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $path = trim((string) ($row['path'] ?? ''));

            if ($path === '' || ! $currentFileMap->has($path)) {
                continue;
            }

            $submittedPaths[] = $path;

            if ((string) ($row['_delete'] ?? '0') === '1') {
                continue;
            }

            $keptRows[] = [
                'path' => $path,
                'sort_order' => (int) ($row['sort_order'] ?? $index),
            ];
        }

        usort($keptRows, static fn (array $first, array $second): int => $first['sort_order'] <=> $second['sort_order']);

        $kept = array_values(array_map(static fn (array $row): string => $row['path'], $keptRows));

        $deleted = collect($currentFiles)
            ->filter(fn ($path): bool => is_string($path) && trim($path) !== '')
            ->reject(fn (string $path): bool => in_array($path, $kept, true))
            ->values()
            ->all();

        if ($rows === []) {
            return [
                'kept' => array_values($currentFiles),
                'deleted' => [],
            ];
        }

        return [
            'kept' => $kept,
            'deleted' => $deleted,
        ];
    }

    private function deleteFiles(array $paths): void
    {
        foreach ($paths as $path) {
            $this->deleteFile((string) $path);
        }
    }

    private function deleteFile(string $path): void
    {
        $path = trim($path);

        if ($path === '') {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function isImageExtension(string $extension): bool
    {
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }
}
