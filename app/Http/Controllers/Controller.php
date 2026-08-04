<?php

namespace App\Http\Controllers;

use App\Support\AdminLanguages;

abstract class Controller
{
    protected readonly AdminLanguages $adminLanguages;

    public function __construct(AdminLanguages $adminLanguages)
    {
        $this->adminLanguages = $adminLanguages;
    }
}
