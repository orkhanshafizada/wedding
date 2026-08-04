<?php

namespace Modules\Menu\Handlers;

use Illuminate\Http\RedirectResponse;
use Modules\Menu\Contracts\MenuTypeHandlerInterface;
use Modules\Menu\Models\Menu;

class CategoriesMenuTypeHandler implements MenuTypeHandlerInterface
{
    public function handle(Menu $menu): RedirectResponse
    {
        return redirect()->route('admin.product.products.index', [
            'main_category_id' => $menu,
        ]);
    }
}
