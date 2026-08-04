<?php

namespace Modules\Menu\Handlers\Web;

use Illuminate\Contracts\View\View;
use Modules\Menu\Contracts\MenuTypeWebHandler;
use Modules\Menu\Models\Menu;
use Modules\Product\Services\ProductService;
use Symfony\Component\HttpFoundation\Response;

class CategoriesMenuWebHandler implements MenuTypeWebHandler
{
    public function __construct(
        private readonly ProductService $productService
    ) {
    }

    public function handle(Menu $menu): Response|View
    {

    }
}
