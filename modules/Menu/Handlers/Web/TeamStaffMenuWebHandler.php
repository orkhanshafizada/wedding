<?php

namespace Modules\Menu\Handlers\Web;

use Illuminate\Contracts\View\View;
use Modules\Menu\Contracts\MenuTypeWebHandler;
use Modules\Menu\Models\Menu;
use Symfony\Component\HttpFoundation\Response;

class TeamStaffMenuWebHandler implements MenuTypeWebHandler
{
    public function handle(Menu $menu): Response|View
    {
        // TODO: Implement web side team staff listing
        return view('web.team-staff', compact('menu'));
    }
}
