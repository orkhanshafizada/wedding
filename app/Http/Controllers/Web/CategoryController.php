<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\Menu\Enums\MenuType;
use Modules\Menu\Models\Menu;

class CategoryController extends Controller
{
    /**
     * Public categories page.
     *
     * Burada menunun type = categories olan
     * aktiv menyular göstərilir.
     */
    public function index(): View
    {
        $categoryMenus = Menu::query()
            ->active()
            ->where('type', MenuType::CATEGORIES)
            ->whereNull('parent_id')
            ->with('translations')
            ->orderBy('sort_order')
            ->get();

        return view('web.categories', [
            'categoryMenus'       => $categoryMenus,
            'pageMetaTitle'       => __('Categories meta title'),
            'pageMetaDescription' => __('Categories meta description'),
        ]);
    }
}
