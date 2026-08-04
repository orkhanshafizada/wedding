<?php

namespace Modules\Menu\Contracts;

use Modules\Menu\Models\Menu;
use Modules\Menu\DTO\MenuDetailContext;

interface MenuTypeApiHandler
{
    public function handle(Menu $menu, MenuDetailContext $context): array;
}
