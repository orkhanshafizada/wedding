<?php
namespace Modules\Menu\Http\Filters\Menu\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\Menu\Http\Filters\Menu\MenuFilterInterface;

final class SearchFilter implements MenuFilterInterface
{
    public function apply(Builder $query, Request $request): Builder
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            return $query;
        }

        $locale = trim((string) $request->query('locale', ''));
        $fallbackLocale = (string) config('app.locale');

        return $query->whereHas('translations', function (Builder $t) use ($q, $locale, $fallbackLocale): void {
            $t->where('name', 'like', '%' . $q . '%');

            if ($locale !== '') {
                $t->whereIn('locale', array_values(array_unique([$locale, $fallbackLocale])));
            }
        });
    }
}
