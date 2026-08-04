<?php

namespace Modules\Menu\Handlers\Admin;

use Illuminate\Http\RedirectResponse;
use Modules\Menu\Contracts\MenuTypeHandlerInterface;
use Modules\Menu\Models\Menu;

class GridsMenuAdminHandler implements MenuTypeHandlerInterface
{
    public function handle(Menu $menu): RedirectResponse
    {
        return redirect()->route('admin.grids.index', $menu);
    }
}
