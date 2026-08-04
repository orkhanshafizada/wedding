<?php
namespace App\Http\Middleware;

use App\Enums\StatusEnum;
use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $raw = (string) $request->header('Content-Language', '');
        $lang = strtolower(substr($raw, 0, 2));

        $allowed = Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->orderBy('sort_order')
            ->pluck('code')
            ->map(static fn ($code) => strtolower(substr((string) $code, 0, 2)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $default = strtolower(substr((string) config('app.fallback_locale', 'az'), 0, 2));

        app()->setLocale(in_array($lang, $allowed, true) ? $lang : $default);

        return $next($request);
    }
}
