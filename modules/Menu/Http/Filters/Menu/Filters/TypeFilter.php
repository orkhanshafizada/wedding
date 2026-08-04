<?php
namespace Modules\Menu\Http\Filters\Menu\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\Menu\Http\Filters\Menu\MenuFilterInterface;

final class TypeFilter implements MenuFilterInterface
{
    public function apply(Builder $query, Request $request): Builder
    {
        $type = trim((string) $request->query('type', ''));

        if ($type === '') {
            return $query;
        }

        return $query->where('type', $type);
    }
}
