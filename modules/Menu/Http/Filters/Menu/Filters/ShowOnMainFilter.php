<?php
namespace Modules\Menu\Http\Filters\Menu\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\Menu\Http\Filters\Menu\MenuFilterInterface;

final class ShowOnMainFilter implements MenuFilterInterface
{
    public function apply(Builder $query, Request $request): Builder
    {
        if (!$request->has('show_on_main_page')) {
            return $query;
        }

        return $query->where('show_on_main_page', $request->boolean('show_on_main_page'));
    }
}
