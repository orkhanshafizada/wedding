<?php
namespace Modules\Menu\Contracts;

use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;
use Modules\Menu\Models\Menu;

/**
 * Interface MenuTypeHandler
 *
 * Hər bir menyu tipi (content, categories, contactus və s.)
 * bu interface-i implement edən ayrıca handler class ilə idarə olunacaq.
 *
 * Məs:
 *  - ContentMenuHandler
 *  - CategoriesMenuHandler
 *  - ContactUsMenuHandler
 */
interface MenuTypeWebHandler
{
    /**
     * Verilmiş Menu modeli üçün uyğun cavabı qaytarır.
     *
     * Burada View, RedirectResponse və ya istənilən HTTP Response
     * qaytara bilərsən.
     *
     * @param  Menu  $menu
     * @return Response|View
     */
    public function handle(Menu $menu): Response|View;
}
