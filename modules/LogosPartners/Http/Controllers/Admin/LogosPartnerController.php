<?php

namespace Modules\LogosPartners\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Upload\ImageUploadService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\LogosPartners\Http\Requests\Admin\StoreLogosPartnerRequest;
use Modules\LogosPartners\Http\Requests\Admin\UpdateLogosPartnerRequest;
use Modules\LogosPartners\Models\LogosPartner;
use Modules\Menu\Models\Menu;

class LogosPartnerController extends Controller
{
    public function __construct(
        private readonly ImageUploadService $imageUploadService
    ) {}

    public function index(Menu $menu): View
    {
        $logosPartners = LogosPartner::query()
            ->where('menu_id', $menu->id)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(20);

        $adminLang = app()->getLocale();

        return view('logospartners::admin.index', compact('logosPartners', 'adminLang', 'menu'));
    }

    public function create(Menu $menu): View
    {
        return view('logospartners::admin.create', compact('menu'));
    }

    public function store(StoreLogosPartnerRequest $request, Menu $menu): RedirectResponse
    {
        $uploadedPath = null;

        try {
            DB::transaction(function () use ($request, $menu, &$uploadedPath): void {
                if ($request->hasFile('image')) {
                    $uploadedPath = $this->imageUploadService->uploadImage($request->file('image'), 'logospartners');
                }

                $maxSortOrder = (int) (LogosPartner::query()
                    ->where('menu_id', $menu->id)
                    ->max('sort_order') ?? 0);

                LogosPartner::create([
                    'menu_id' => $menu->id,
                    'name' => $request->validated('name') ?? [],
                    'description' => $request->validated('description') ?? [],
                    'slug' => $request->validated('slug') ?? [],
                    'link' => $request->validated('link') ?? [],
                    'image' => $uploadedPath,
                    'is_active' => (bool) ((int) ($request->validated('is_active') ?? 0) === 1),
                    'sort_order' => $maxSortOrder + 1,
                ]);
            });

            return redirect()
                ->route('admin.logospartners.index', $menu)
                ->with('success', __('Logos Partner created successfully'));
        } catch (\Throwable $e) {
            if (!empty($uploadedPath)) {
                Storage::disk('public')->delete($uploadedPath);
            }

            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function edit(Menu $menu, LogosPartner $logosPartner): View
    {
        abort_if((int) $logosPartner->menu_id !== (int) $menu->id, 404);

        return view('logospartners::admin.edit', compact('logosPartner', 'menu'));
    }

    public function update(UpdateLogosPartnerRequest $request, Menu $menu, LogosPartner $logosPartner): RedirectResponse
    {
        abort_if((int) $logosPartner->menu_id !== (int) $menu->id, 404);

        $newUploadedPath = null;
        $oldPath = (string) ($logosPartner->image ?? '');

        try {
            DB::transaction(function () use ($request, $logosPartner, &$newUploadedPath, $oldPath): void {
                $imagePath = $oldPath;

                if ($request->hasFile('image')) {
                    $newUploadedPath = $this->imageUploadService->uploadImage($request->file('image'), 'logospartners');
                    $imagePath = $newUploadedPath;
                }

                $logosPartner->update([
                    'name' => $request->validated('name') ?? [],
                    'description' => $request->validated('description') ?? [],
                    'slug' => $request->validated('slug') ?? [],
                    'link' => $request->validated('link') ?? [],
                    'image' => $imagePath,
                    'is_active' => (bool) ((int) ($request->validated('is_active') ?? 0) === 1),
                ]);
            });

            if (!empty($newUploadedPath) && !empty($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            return redirect()
                ->route('admin.logospartners.index', $menu)
                ->with('success', __('Logos Partner updated successfully'));
        } catch (\Throwable $e) {
            if (!empty($newUploadedPath)) {
                Storage::disk('public')->delete($newUploadedPath);
            }

            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy(Menu $menu, LogosPartner $logosPartner): RedirectResponse
    {
        abort_if((int) $logosPartner->menu_id !== (int) $menu->id, 404);

        $path = (string) ($logosPartner->image ?? '');

        try {
            DB::transaction(function () use ($logosPartner): void {
                $logosPartner->delete();
            });

            if ($path !== '') {
                Storage::disk('public')->delete($path);
            }

            return redirect()
                ->route('admin.logospartners.index', $menu)
                ->with('success', __('Logos Partner deleted successfully'));
        } catch (\Throwable $e) {
            return back()->with('error', __('An error occurred'));
        }
    }

    public function updateOrder(Request $request, Menu $menu): JsonResponse
    {
        $order = (array) $request->input('order', []);

        foreach ($order as $item) {
            $id = (int) ($item['id'] ?? 0);
            $sort = (int) ($item['sort_order'] ?? 0);

            if ($id <= 0) {
                continue;
            }

            LogosPartner::query()
                ->where('id', $id)
                ->where('menu_id', $menu->id)
                ->update(['sort_order' => $sort]);
        }

        return response()->json(['success' => true]);
    }
}
