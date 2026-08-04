<?php
namespace Modules\Menu\Http\Filters\Menu\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\Menu\Http\Filters\Menu\MenuFilterInterface;

final class InFooterFilter implements MenuFilterInterface
{
    public function apply(Builder $query, Request $request): Builder
    {
        if (! $request->has('in_footer')) {
            return $query;
        }

        return $query->where('in_footer', (bool) $request->boolean('in_footer'));
    }
}
