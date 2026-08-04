<?php

namespace Modules\MainPage\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\MainPage\Http\Requests\Admin\StoreMainPageSectionRequest;
use Modules\MainPage\Http\Requests\Admin\UpdateMainPageSectionOrderRequest;
use Modules\MainPage\Http\Requests\Admin\UpdateMainPageSectionRequest;
use Modules\MainPage\Models\MainPageSection;
use Modules\MainPage\Services\MainPageSectionService;

class MainPageSectionController extends Controller
{
    public function __construct(
        protected readonly MainPageSectionService $service
    ) {
    }

    public function index(): View
    {
        $sections = $this->service->paginate();

        return view('mainpage::admin.sections.index', [
            'sections' => $sections,
            'adminLang' => app()->getLocale(),
        ]);
    }

    public function create(): View
    {
        return view('mainpage::admin.sections.create', [
            'languages' => Language::query()->orderBy('sort_order')->orderBy('id')->get(),
            'sourceTypeOptions' => $this->service->sourceTypeOptions(),
            'menuTypeOptions' => $this->service->menuTypeOptions(),
        ]);
    }

    public function store(StoreMainPageSectionRequest $request): RedirectResponse
    {
        $this->service->create(
            $request->validated(),
            (array) $request->input('title', [])
        );

        return redirect()
            ->route('admin.main_page.sections.index')
            ->with('success', __('Main page section created successfully.'));
    }

    public function edit(MainPageSection $section): View
    {
        $section->load(['translations.language']);

        return view('mainpage::admin.sections.edit', [
            'section' => $section,
            'languages' => Language::query()->orderBy('sort_order')->orderBy('id')->get(),
            'sourceTypeOptions' => $this->service->sourceTypeOptions(),
            'menuTypeOptions' => $this->service->menuTypeOptions(),
        ]);
    }

    public function update(UpdateMainPageSectionRequest $request, MainPageSection $section): RedirectResponse
    {
        $this->service->update(
            $section,
            $request->validated(),
            (array) $request->input('title', [])
        );

        return redirect()
            ->route('admin.main_page.sections.index')
            ->with('success', __('Main page section updated successfully.'));
    }

    public function destroy(MainPageSection $section): RedirectResponse
    {
        $this->service->delete($section);

        return redirect()
            ->route('admin.main_page.sections.index')
            ->with('success', __('Main page section deleted successfully.'));
    }

    public function updateOrder(UpdateMainPageSectionOrderRequest $request): JsonResponse
    {
        $this->service->updateOrder((array) $request->validated('order'));

        return response()->json([
            'success' => true,
        ]);
    }

    public function sourceReferences(Request $request, string $sourceType): JsonResponse
    {
        return response()->json([
            'success' => true,
            'items' => $this->service->sourceReferences(
                $sourceType,
                $request->query('menu_type')
            ),
        ]);
    }
}
