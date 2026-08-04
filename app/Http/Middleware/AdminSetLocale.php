<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;

class AdminSetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $sessionKey = 'admin_locale';

        $active = Language::query()
            ->active()
            ->orderBy('sort_order')
            ->get(['code', 'is_rtl', 'is_default_admin', 'status']);

        $count  = $active->count();

        $locale = (string) $request->query('lang', (string) $request->session()->get($sessionKey, ''));
        $locale = trim($locale);

        if ($locale !== '') {
            $locale = mb_strtolower($locale);
        }

        if ($locale === '') {
            $default = $active->firstWhere('is_default_admin', true) ?: $active->first();

            if ($default) {
                $locale = (string) $default->code;
                $locale = $locale !== '' ? mb_strtolower($locale) : '';
                $request->session()->put($sessionKey, $locale);
            }
        } else {
            $exists = $active->contains(function ($l) use ($locale) {
                $code = trim((string) ($l->code ?? ''));
                return $code !== '' && mb_strtolower($code) === $locale;
            });

            if ($exists) {
                $request->session()->put($sessionKey, $locale);
            } else {
                $default = $active->firstWhere('is_default_admin', true) ?: $active->first();
                $fallback = $default ? (string) $default->code : (string) config('app.locale');
                $fallback = trim($fallback) !== '' ? mb_strtolower(trim($fallback)) : '';
                $locale = $fallback;
                $request->session()->put($sessionKey, $locale);
            }
        }

        if ($locale !== '') {
            app()->setLocale($locale);
        }

        $rtl = (bool) optional(
            $active->first(function ($l) use ($locale) {
                $code = trim((string) ($l->code ?? ''));
                return $code !== '' && mb_strtolower($code) === $locale;
            })
        )->is_rtl;

        view()->share('admin_is_rtl', $rtl);
        view()->share('admin_lang_count', $count);
        view()->share('admin_locale', $locale);

        return $next($request);
    }
}
