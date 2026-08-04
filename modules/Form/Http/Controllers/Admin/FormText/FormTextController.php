<?php

namespace Modules\Form\Http\Controllers\Admin\FormText;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Form\Models\FormText;
use Modules\Menu\Models\Menu;

class FormTextController extends Controller
{
    public function edit(Menu $menu): View
    {
        $formText = FormText::with('translations')->where('menu_id', $menu->id)->first();

        if (!$formText) {
            $formText = new FormText(['menu_id' => $menu->id]);
        }

        return view('form::admin.form-text.edit', compact('menu', 'formText'));
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $languages = Language::query()
            ->active()
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->values()
            ->all();

        $headerTexts = (array) $request->input('header_text', []);
        $successTexts = (array) $request->input('success_text', []);
        $emailTexts = (array) $request->input('email_text', []);

        $translations = [];
        foreach ($languages as $locale) {
            $translations[] = [
                'locale' => $locale,
                'header_text' => trim((string) ($headerTexts[$locale] ?? '')),
                'success_text' => trim((string) ($successTexts[$locale] ?? '')),
                'email_text' => trim((string) ($emailTexts[$locale] ?? '')),
            ];
        }

        DB::transaction(function () use ($menu, $translations) {
            $formText = FormText::firstOrCreate(['menu_id' => $menu->id]);

            foreach ($translations as $t) {
                $formText->translations()
                    ->updateOrCreate(
                        ['locale' => $t['locale']],
                        [
                            'header_text' => $t['header_text'],
                            'success_text' => $t['success_text'],
                            'email_text' => $t['email_text'],
                        ]
                    );
            }
        });

        return redirect()->route('admin.form.text.edit', $menu)
            ->with('success', __('Form text saved successfully'));
    }
}
