<?php

namespace Modules\Menu\Handlers\Web;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Menu\Contracts\MenuTypeWebHandler;
use Modules\Menu\Models\Menu;
use Symfony\Component\HttpFoundation\Response;

class LinkMenuWebHandler implements MenuTypeWebHandler
{
    /**
     * type = link olan menyular üçün yönləndirmə loqikası.
     *
     * Prioritet:
     *  1. Tərcümənin link sahəsi
     *  2. Menu modelinin link sahəsi
     *  3. Default: '/'
     *
     * @param  Menu  $menu
     * @return Response|View
     */
    public function handle(Menu $menu): Response|View
    {
        $locale = app()->getLocale();

        $translation = $menu->translations
            ->firstWhere('locale', $locale)
            ?? $menu->translations->first();

        $url = $translation?->link
            ?? $menu->link
            ?? '/';

        // Boş qalmaması üçün fallback
        if (empty($url)) {
            $url = '/';
        }

        // Tam URL-dirsə (http, https və ya //domain) – external redirect
        if (preg_match('#^(https?:)?//#i', $url)) {
            /** @var RedirectResponse $response */
            $response = redirect()->away($url);

            return $response;
        }

        // Nisbi yoldursa – normal redirect
        // Başında / yoxdursa, əlavə edirik
        if ($url[0] !== '/') {
            $url = '/' . ltrim($url, '/');
        }

        /** @var RedirectResponse $response */
        $response = redirect($url);

        return $response;
    }
}
