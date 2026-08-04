<?php

namespace Modules\Menu\Handlers\Admin;

use Illuminate\Http\RedirectResponse;
use Modules\Menu\Contracts\MenuTypeHandlerInterface;
use Modules\Menu\Models\Menu;

class ContactMenuAdminHandler implements MenuTypeHandlerInterface
{
    /**
     * type = contact olan menyu üçün admin "Content/Contact" düyməsinə klik ediləndə
     * hara yönləndirəcəyimizi müəyyənləşdirir.
     *
     * Bizdə məqsəd: sadəcə contact message-lər cədvəlinə getməkdir.
     */
    public function handle(Menu $menu): RedirectResponse
    {
        // Bu halda konkret menyu ID-si lazım deyil, global cədvələ aparırıq
        return redirect()->route('admin.contact-messages.index', $menu);
    }
}
