<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminLocaleController extends Controller
{
    public function set(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'locale' => ['required','string','max:20'],
            'return' => ['nullable','string'],
        ]);

        $code = $validated['locale'];
        $ok = Language::active()->where('code', $code)->exists();

        if (!$ok) {
            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => __('Invalid language')], 422);
            }
            return back()->with('error', __('Invalid language'));
        }

        $request->session()->put('admin_locale', $code);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => __('Language updated')]);
        }

        $return = $validated['return'] ?? url()->previous();
        return redirect($return)->with('success', __('Language updated'));
    }
}
