<?php

namespace Modules\Menu\Handlers\Admin;

use Illuminate\Http\RedirectResponse;
use Modules\Menu\Contracts\MenuTypeHandlerInterface;
use Modules\Menu\Models\Menu;

class LogosPartnersMenuAdminHandler implements MenuTypeHandlerInterface
{
    public function handle(Menu $menu): RedirectResponse
    {
        return redirect()->route('admin.logospartners.index', $menu);
    }
}
