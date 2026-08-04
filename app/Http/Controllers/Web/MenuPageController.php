<?php

namespace App\Http\Controllers\Web;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Menu\Services\MenuWebPageResolver;
use Symfony\Component\HttpFoundation\Response;

/**
 * MenuPageController
 *
 * Front tərəfdə dinamik menyu routeları üçün fallback controller.
 *
 * Məs:
 *  - /about-us  -> link = "/about-us"  -> resolver -> uyğun handler
 *  - /artists   -> link = "/artists"   -> resolver -> uyğun handler
 *
 * Bütün ağıllı məntiq MenuWebPageResolver + handler-lərin içindədir.
 */
class MenuPageController extends Controller
{
    public function __construct(
        protected readonly MenuWebPageResolver $resolver
    ) {
    }

    /**
     * Fallback action – bütün uyğun gələn URL-lər üçün çağırılacaq.
     *
     * Route-da {any} parametri olacaq, amma biz əslində
     * həmişə $request->path() istifadə edirik ki, əlavə
     * parametrlərdən asılı qalmayaq.
     *
     * @param  Request  $request
     * @param  string|null  $any
     * @return Response|View
     */
    public function __invoke(Request $request, ?string $any = null): Response|View
    {
        // Məs: "" (root), "about-us", "foo/bar"
        $path = $request->path();

        // Root ("/") üçün bu controller işə düşməməlidir – home ayrı route-dadır.
        if ($path === '/') {
            abort(404);
        }

        // Path-i həmişə "/" ilə başlayan linkə çeviririk
        // "about-us" -> "/about-us", "foo/bar" -> "/foo/bar"
        $link = '/'.ltrim($path, '/');

        // Bütün məntiq resolver + handler-lərdədir
        return $this->resolver->handleByLink($link);
    }
}
