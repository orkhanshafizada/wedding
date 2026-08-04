<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Language\StoreLanguageRequest;
use App\Http\Requests\Admin\Language\UpdateLanguageRequest;
use App\Models\Language;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LanguagesController extends Controller
{
    public function index(): View
    {
        $locales = Language::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.languages.index', compact('locales'));
    }

    public function create(): View
    {
        return view('admin.languages.create');
    }

    public function store(StoreLanguageRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_required'] = (bool) ($data['is_required'] ?? false);

        Language::create($data);

        return redirect()
            ->route('admin.languages.index')
            ->with('success', __('Language created successfully.'));
    }

    public function edit(Language $language): View
    {
        return view('admin.languages.edit', compact('language'));
    }

    public function update(UpdateLanguageRequest $request, Language $language): RedirectResponse
    {
        $data = $request->validated();
        $data['is_required'] = (bool) ($data['is_required'] ?? false);

        $language->update($data);

        return redirect()
            ->route('admin.languages.index')
            ->with('success', __('Language updated successfully.'));
    }

    public function destroy(Language $language): RedirectResponse
    {
        if ($language->is_default_admin || $language->is_default_site) {
            return back()->with('error', __('Can\'t delete the default language'));
        }

        $language->delete();

        return redirect()
            ->route('admin.languages.index')
            ->with('success', __('Language deleted successfully.'));
    }

    public function toggleStatus(Request $request, Language $language): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        if (($language->is_default_admin || $language->is_default_site) && $validated['status'] === 'Inactive') {
            return response()->json([
                'ok' => false,
                'message' => __('Default admin language is active.'),
            ], 422);
        }

        $language->update(['status' => $validated['status']]);

        return response()->json([
            'ok' => true,
            'message' => __('Status updated successfully.'),
            'status' => $language->status,
        ]);
    }

    public function toggleRequired(Request $request, Language $language): JsonResponse
    {
        $validated = $request->validate([
            'is_required' => ['required', 'boolean'],
        ]);

        $language->update(['is_required' => (bool) $validated['is_required']]);

        return response()->json([
            'ok' => true,
            'message' => __('Required updated successfully.'),
            'is_required' => (bool) $language->is_required,
        ]);
    }

    public function setDefaultAdmin(Language $language): RedirectResponse
    {
        if ($language->status !== 'Active') {
            return back()->with('error', __('Default admin language is not active.'));
        }

        DB::transaction(static function () use ($language): void {
            Language::where('is_default_admin', true)->update(['is_default_admin' => false]);
            $language->update(['is_default_admin' => true]);
        });

        return back()->with('success', __('Default admin language is updated successfully.'));
    }

    public function setDefaultSite(Language $language): RedirectResponse
    {
        if ($language->status !== 'Active') {
            return back()->with('error', __('Default site language is not active.'));
        }

        DB::transaction(static function () use ($language): void {
            Language::where('is_default_site', true)->update(['is_default_site' => false]);
            $language->update(['is_default_site' => true]);
        });

        return back()->with('success', __('Default site language is updated successfully.'));
    }
}
