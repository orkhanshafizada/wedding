<?php

namespace Modules\Menu\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\Menu\Models\Menu;
use Modules\Menu\Services\Admin\MenuTypeRouter;

class MenuRouteController extends Controller
{
    public function __invoke(Menu $menu, MenuTypeRouter $router): RedirectResponse
    {
        return $router->redirect($menu);
    }
}
