<?php

namespace Modules\Menu\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use App\Support\FontAwesome;

class MenuAjaxController extends Controller
{
    public function fontAwesomeIcons(): JsonResponse
    {
        $version = request()->string('version')->toString();

        return response()->json([
            'icons' => FontAwesome::icons($version ?: 'v6'),
        ]);
    }
}
