<?php
namespace Modules\Menu\Http\Filters\Menu\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\Menu\Http\Filters\Menu\MenuFilterInterface;

final class ParentIdFilter implements MenuFilterInterface
{
    public function apply(Builder $query, Request $request): Builder
    {
        if (! $request->has('parent_id')) {
            return $query;
        }

        $parentId = $request->integer('parent_id');

        return $query->where('parent_id', $parentId > 0 ? $parentId : null);
    }
}
