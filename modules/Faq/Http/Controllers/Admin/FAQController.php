<?php

namespace Modules\FAQ\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\FAQ\Models\FAQ;
use Modules\Menu\Models\Menu;

class FAQController extends Controller
{
    public function index(Menu $menu): View
    {
        $faqs = FAQ::where('menu_id', $menu->id)
            ->orderBy('sort_order')
            ->orderBy('id', 'desc')
            ->paginate(20);

        $adminLang = app()->getLocale();

        return view('faq::admin.faq.index', compact('faqs', 'adminLang', 'menu'));
    }

    public function create(Menu $menu): View
    {
        return view('faq::admin.faq.create', compact('menu'));
    }

    public function store(Request $request, Menu $menu): RedirectResponse
    {
        $requiredLocales = (array) (view()->shared('requiredLanguageCodes')?->all() ?? []);

        $rules = [
            'question' => 'required|array',
            'question.*' => 'nullable|string|max:500',
            'answer' => 'required|array',
            'answer.*' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];

        $messages = [];

        foreach ($requiredLocales as $locale) {
            $rules["question.$locale"] = 'required|string|max:500';
            $rules["answer.$locale"] = 'required|string';

            $messages["question.$locale.required"] = "Sual ($locale) mütləqdir";
            $messages["answer.$locale.required"] = "Cavab ($locale) mütləqdir";
        }

        $validated = $request->validate($rules, $messages);

        $maxSortOrder = FAQ::where('menu_id', $menu->id)->max('sort_order') ?? 0;

        FAQ::create([
            'menu_id' => $menu->id,
            'question' => $request->input('question'),
            'answer' => $request->input('answer'),
            'is_active' => $request->input('is_active', false),
            'sort_order' => $maxSortOrder + 1,
        ]);

        return redirect()->route('admin.faq.index', $menu)->with('success', 'FAQ uğurla yaradıldı');
    }

    public function edit(Menu $menu, FAQ $faq): View
    {
        if ($faq->menu_id !== $menu->id) {
            abort(404);
        }

        return view('faq::admin.faq.edit', compact('faq', 'menu'));
    }

    public function update(Request $request, Menu $menu, FAQ $faq): RedirectResponse
    {
        if ($faq->menu_id !== $menu->id) {
            abort(404);
        }

        $requiredLocales = (array) (view()->shared('requiredLanguageCodes')?->all() ?? []);

        $rules = [
            'question' => 'required|array',
            'question.*' => 'nullable|string|max:500',
            'answer' => 'required|array',
            'answer.*' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];

        $messages = [];

        foreach ($requiredLocales as $locale) {
            $rules["question.$locale"] = 'required|string|max:500';
            $rules["answer.$locale"] = 'required|string';

            $messages["question.$locale.required"] = "Sual ($locale) mütləqdir";
            $messages["answer.$locale.required"] = "Cavab ($locale) mütləqdir";
        }

        $validated = $request->validate($rules, $messages);

        $faq->update([
            'question' => $request->input('question'),
            'answer' => $request->input('answer'),
            'is_active' => $request->input('is_active', false),
        ]);

        return redirect()->route('admin.faq.index', $menu)->with('success', 'FAQ uğurla yeniləndi');
    }

    public function destroy(Menu $menu, FAQ $faq): RedirectResponse
    {
        if ($faq->menu_id !== $menu->id) {
            abort(404);
        }

        $faq->delete();

        return redirect()->route('admin.faq.index', $menu)->with('success', 'FAQ uğurla silindi');
    }

    public function updateOrder(Request $request, Menu $menu)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:faqs,id',
            'order.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->order as $item) {
            FAQ::where('id', $item['id'])
                ->where('menu_id', $menu->id)
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}
