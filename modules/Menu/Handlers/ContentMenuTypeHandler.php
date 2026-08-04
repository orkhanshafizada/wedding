<?php

namespace Modules\Menu\Handlers;

use Illuminate\Http\RedirectResponse;
use Modules\Menu\Contracts\MenuTypeHandlerInterface;
use Modules\Menu\Models\Menu;

class ContentMenuTypeHandler implements MenuTypeHandlerInterface
{
    public function handle(Menu $menu): RedirectResponse
    {
        return redirect()->route('admin.menus.content.edit', ['menu' => $menu]);
    }
}
