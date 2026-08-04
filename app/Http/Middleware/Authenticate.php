<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // 🔹 Customer account zonası
        if ($request->is('account') || $request->is('account/*')) {
            return route('account.login');
        }

        // 🔹 Admin zonası
        if ($request->is('ayti') || $request->is('ayti/*')) {
            return route('admin.login');
        }

        // 🔹 Qalan public hissə
        return url('/');
    }
}
