<?php

namespace Modules\Menu\Contracts;

use Illuminate\Http\RedirectResponse;
use Modules\Menu\Models\Menu;

interface MenuTypeHandlerInterface
{
    public function handle(Menu $menu): RedirectResponse;
}
