<?php

namespace App\Http\Middleware;

use App\Enums\UserStatusEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('admin.login');
        }

        if ($user->status !== UserStatusEnum::Active) {
            auth()->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login')
                ->withErrors(['email' => __('Auth failed')]);
        }

        if (! $user->adminRoles()->where('admin_roles.is_active', true)->exists()) {
            auth()->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login')
                ->withErrors(['email' => __('Auth failed')]);
        }

        return $next($request);
    }
}
