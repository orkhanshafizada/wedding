<?php

namespace Modules\AdminPermission\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\AdminPermission\Services\AdminAccessService;
use Symfony\Component\HttpFoundation\Response;

class AdminPermissionMiddleware
{
    public function __construct(private readonly AdminAccessService $accessService)
    {
    }

    public function handle(Request $request, Closure $next, string $ability, ?string $routeParameter = null): Response
    {
        $user = $request->user();

        abort_unless($user, 403);

        $argument = $routeParameter ? $request->route($routeParameter) : null;

        if ($ability === 'menu.view' && $argument === null) {
            abort_unless(
                $this->accessService->can($user, 'menu.view') || $this->accessService->hasAnyMenuPermission($user),
                403
            );

            return $next($request);
        }

        abort_unless($this->accessService->can($user, $ability, $argument), 403);

        return $next($request);
    }
}
