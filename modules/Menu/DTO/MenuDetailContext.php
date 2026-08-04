<?php

namespace Modules\Menu\DTO;

use Illuminate\Http\Request;

class MenuDetailContext
{
    public function __construct(
        public readonly Request $request,
        public readonly string $locale,
        public readonly string $fallbackLocale
    ) {
    }

    public function perPage(int $default = 12): int
    {
        $perPage = (int) $this->request->query('per_page', $default);

        if ($perPage < 1) {
            $perPage = 1;
        }

        if ($perPage > 100) {
            $perPage = 100;
        }

        return $perPage;
    }

    public function page(int $default = 1): int
    {
        $page = (int) $this->request->query('page', $default);

        if ($page < 1) {
            $page = 1;
        }

        return $page;
    }
}
