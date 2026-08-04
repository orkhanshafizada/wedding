<?php
namespace Modules\Menu\Http\Filters\Menu;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\Menu\Http\Filters\Menu\Filters\InFooterFilter;
use Modules\Menu\Http\Filters\Menu\Filters\InHeaderFilter;
use Modules\Menu\Http\Filters\Menu\Filters\LinkFilter;
use Modules\Menu\Http\Filters\Menu\Filters\ParentIdFilter;
use Modules\Menu\Http\Filters\Menu\Filters\SearchFilter;
use Modules\Menu\Http\Filters\Menu\Filters\ShowOnMainFilter;
use Modules\Menu\Http\Filters\Menu\Filters\StatusFilter;
use Modules\Menu\Http\Filters\Menu\Filters\TypeFilter;
use Modules\Menu\Http\Filters\Menu\Filters\ViewTypeFilter;

final class MenuQueryFilter
{
    /**
     * @var array<int, class-string<MenuFilterInterface>>
     */
    private array $filters = [
        StatusFilter::class,
        InHeaderFilter::class,
        InFooterFilter::class,
        ShowOnMainFilter::class,
        TypeFilter::class,
        ViewTypeFilter::class,
        ParentIdFilter::class,
        LinkFilter::class,
        SearchFilter::class,
    ];

    public function __construct(
        private readonly Request $request
    ) {
    }

    public function apply(Builder $query): Builder
    {
        foreach ($this->filters as $filterClass) {
            $filter = app($filterClass);

            if (! $filter instanceof MenuFilterInterface) {
                continue;
            }

            $query = $filter->apply($query, $this->request);
        }

        return $query;
    }
}
