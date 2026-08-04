<?php

namespace Modules\Menu\Handlers\Admin;

use Illuminate\Http\RedirectResponse;
use Modules\Menu\Contracts\MenuTypeHandlerInterface;
use Modules\Menu\Models\Menu;

class FaqMenuAdminHandler implements MenuTypeHandlerInterface
{
    /**
     * type = faq olan menyu üçün admin "FAQ" düyməsinə klik ediləndə
     * həmin menyuya aid FAQ-ların siyahısına yönləndirir.
     */
    public function handle(Menu $menu): RedirectResponse
    {
        // Konkret menyu üçün FAQ-ları göstərmək
        return redirect()->route('admin.faq.index', $menu);
    }
}
