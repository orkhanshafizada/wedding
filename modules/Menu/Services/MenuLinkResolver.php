<?php

namespace Modules\Menu\Services;

use Modules\Menu\Models\Menu;

class MenuLinkResolver
{
    public function findByLinkOrFail(string $link): Menu
    {
        $normalized = $this->normalizeLink($link);
        $alt = ltrim($normalized, '/');

        return Menu::query()
            ->with(['translations'])
            ->whereHas('translations', function ($query) use ($normalized, $alt): void {
                $query->whereIn('link', [$normalized, $alt]);
            })
            ->firstOrFail();
    }

    private function normalizeLink(string $link): string
    {
        $link = trim($link);

        if ($link === '') {
            return '/';
        }

        if (! str_starts_with($link, '/')) {
            $link = '/' . $link;
        }

        return $link;
    }
}
