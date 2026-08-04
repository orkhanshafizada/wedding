<?php
namespace Modules\Menu\Http\Filters\Menu;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

interface MenuFilterInterface
{
    public function apply(Builder $query, Request $request): Builder;
}
