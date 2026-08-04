<?php
namespace Modules\Menu\Http\Filters\Menu\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\Menu\Http\Filters\Menu\MenuFilterInterface;

final class LinkFilter implements MenuFilterInterface
{
    public function apply(Builder $query, Request $request): Builder
    {
        $link = trim((string) $request->query('link', ''));

        if ($link === '') {
            return $query;
        }

        $normalized = str_starts_with($link, '/') ? $link : '/' . ltrim($link, '/');
        $alt = ltrim($normalized, '/');

        return $query->where(function (Builder $q) use ($normalized, $alt): void {
            $q->whereIn('link', [$normalized, $alt])
                ->orWhereHas('translations', function (Builder $t) use ($normalized, $alt): void {
                    $t->whereIn('link', [$normalized, $alt]);
                });
        });
    }
}
