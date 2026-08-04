<?php
namespace Modules\Menu\Http\Filters\Menu\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\Menu\Http\Filters\Menu\MenuFilterInterface;

final class StatusFilter implements MenuFilterInterface
{
    public function apply(Builder $query, Request $request): Builder
    {
        if ($request->has('status')) {
            return $query->where('status', $request->boolean('status'));
        }

        return $query->where('status', true);
    }
}
