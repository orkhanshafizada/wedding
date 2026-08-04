<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        if (auth()->check()) {
            // Admin zonası üçün
            if ($request->is('ayti/login')) {
                return redirect()->route('admin.dashboard');
            }

            // Gələcəkdə customer login üçün buraya başqa yoxlama əlavə edə bilərsən
        }

        return $next($request);
    }
}
