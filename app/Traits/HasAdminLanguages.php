<?php

namespace App\Traits;

use App\Support\AdminLanguages;

trait HasAdminLanguages
{
    protected function adminLanguages(): AdminLanguages
    {
        return app(AdminLanguages::class);
    }
}
