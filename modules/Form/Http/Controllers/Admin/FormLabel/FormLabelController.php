<?php

namespace Modules\Form\Http\Controllers\Admin\FormLabel;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Form\Enums\FormLabelTypeEnum;
use Modules\Form\Models\FormLabel;
use Modules\Menu\Models\Menu;

class FormLabelController extends Controller
{
    public function index(Menu $menu): View
    {
        $labels = FormLabel::with(['translations' => function ($query) {
            $query->where('locale', app()->getLocale());
        }])
            ->where('menu_id', $menu->id)
            ->orderBy('sort_order')
            ->get();

        return view('form::admin.form-label.index', compact('menu', 'labels'));
    }

    public function create(Menu $menu): View
    {
        $types = FormLabelTypeEnum::getOptions();

        return view('form::admin.form-label.create', compact('menu', 'types'));
    }

    public function store(Request $request, Menu $menu): RedirectResponse
    {
        $requiredLocales = Language::query()
            ->isRequired()
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->values()
            ->all();

        $rules = [
            'type' => 'required|string',
            'is_required' => 'nullable|boolean',
            'send_text_mail' => 'nullable|boolean',
            'name' => 'required|array',
            'name.*' => 'nullable|string|max:255',
            'information' => 'nullable|array',
            'information.*' => 'nullable|string',
        ];

        $messages = [
            'type.required' => __('Type is required'),
            'name.required' => __('Name is required'),
            'name.array' => __('Name must be an array'),
        ];

        foreach ($requiredLocales as $locale) {
            $rules["name.{$locale}"] = 'required|string|max:255';
            $messages["name.{$locale}.required"] = __('Name (:locale) is required', ['locale' => $locale]);
        }

        $request->validate($rules, $messages);

        $languages = Language::query()
            ->active()
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->values()
            ->all();

        $names = (array) $request->input('name', []);
        $informations = (array) $request->input('information', []);

        $translations = [];
        foreach ($languages as $locale) {
            $translations[] = [
                'locale' => $locale,
                'name' => trim((string) ($names[$locale] ?? '')),
                'information' => trim((string) ($informations[$locale] ?? '')),
            ];
        }

        DB::transaction(function () use ($menu, $request, $translations) {
            $maxSortOrder = FormLabel::where('menu_id', $menu->id)->max('sort_order') ?? 0;

            $label = FormLabel::create([
                'menu_id' => $menu->id,
                'type' => $request->input('type'),
                'is_required' => (bool) $request->input('is_required', false),
                'send_text_mail' => (bool) $request->input('send_text_mail', false),
                'sort_order' => $maxSortOrder + 1,
            ]);

            foreach ($translations as $t) {
                $label->translations()->create($t);
            }
        });

        return redirect()->route('admin.form.index', $menu)
            ->with('success', __('Label created successfully'));
    }

    public function edit(Menu $menu, FormLabel $label): View
    {
        $label->load('translations');

        $types = FormLabelTypeEnum::getOptions();

        return view('form::admin.form-label.edit', compact('menu', 'label', 'types'));
    }

    public function update(Request $request, Menu $menu, FormLabel $label): RedirectResponse
    {
        $requiredLocales = Language::query()
            ->isRequired()
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->values()
            ->all();

        $rules = [
            'type' => 'required|string',
            'is_required' => 'nullable|boolean',
            'send_text_mail' => 'nullable|boolean',
            'name' => 'required|array',
            'name.*' => 'nullable|string|max:255',
            'information' => 'nullable|array',
            'information.*' => 'nullable|string',
        ];

        $messages = [
            'type.required' => __('Type is required'),
            'name.required' => __('Name is required'),
            'name.array' => __('Name must be an array'),
        ];

        foreach ($requiredLocales as $locale) {
            $rules["name.{$locale}"] = 'required|string|max:255';
            $messages["name.{$locale}.required"] = __('Name (:locale) is required', ['locale' => $locale]);
        }

        $request->validate($rules, $messages);

        $languages = Language::query()
            ->active()
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->values()
            ->all();

        $names = (array) $request->input('name', []);
        $informations = (array) $request->input('information', []);

        $translations = [];
        foreach ($languages as $locale) {
            $translations[] = [
                'locale' => $locale,
                'name' => trim((string) ($names[$locale] ?? '')),
                'information' => trim((string) ($informations[$locale] ?? '')),
            ];
        }

        DB::transaction(function () use ($label, $request, $translations) {
            $label->update([
                'type' => $request->input('type'),
                'is_required' => (bool) $request->input('is_required', false),
                'send_text_mail' => (bool) $request->input('send_text_mail', false),
            ]);

            foreach ($translations as $t) {
                $label->translations()->updateOrCreate(
                    ['locale' => $t['locale']],
                    [
                        'name' => $t['name'],
                        'information' => $t['information'],
                    ]
                );
            }
        });

        return redirect()->route('admin.form.index', $menu)
            ->with('success', __('Label updated successfully'));
    }

    public function destroy(Menu $menu, FormLabel $label): RedirectResponse
    {
        $label->delete();

        return redirect()->route('admin.form.index', $menu)
            ->with('success', __('Label deleted successfully'));
    }

    public function updateOrder(Request $request, Menu $menu): JsonResponse
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:form_labels,id',
            'order.*.position' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->input('order') as $item) {
                FormLabel::where('id', $item['id'])->update(['sort_order' => $item['position']]);
            }
        });

        return response()->json(['success' => true, 'message' => __('Order updated successfully')]);
    }
}
