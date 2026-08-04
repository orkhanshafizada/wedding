<?php

namespace Modules\Gallery\Services;

use Illuminate\Http\RedirectResponse;
use Modules\Menu\Contracts\MenuTypeHandlerInterface;
use Modules\Menu\Models\Menu;

class GalleryMenuAdminHandler implements MenuTypeHandlerInterface
{
    public function handle(Menu $menu): RedirectResponse
    {
        return redirect()->route('admin.gallery.index', $menu);
    }

    public function getAdminUrl(Menu $menu): string
    {
        return route('admin.gallery.index', $menu);
    }

    public function getAdminLabel(): string
    {
        return __('Manage Albums');
    }
}
