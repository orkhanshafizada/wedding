<?php
namespace Modules\Menu\Http\Filters\Menu\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\Menu\Http\Filters\Menu\MenuFilterInterface;

final class InHeaderFilter implements MenuFilterInterface
{
    public function apply(Builder $query, Request $request): Builder
    {
        if (! $request->has('in_header')) {
            return $query;
        }

        return $query->where('in_header', (bool) $request->boolean('in_header'));
    }
}
