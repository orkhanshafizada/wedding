<?php

namespace Modules\Log\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Log\Services\AdminSessionService;
use Symfony\Component\HttpFoundation\Response;

final class AdminSessionHeartbeat
{
    private const SESSION_KEY = '__admin_session_heartbeat_at';

    public function __construct(
        private readonly AdminSessionService $adminSessionService
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $routeName = (string) ($request->route()?->getName() ?? '');

        if (! str_starts_with($routeName, 'admin.')) {
            return;
        }

        $user = Auth::user();

        if (! $user || ! $user->adminRoles()->where('admin_roles.is_active', true)->exists()) {
            return;
        }

        $now = time();
        $lastHeartbeatAt = (int) $request->session()->get(self::SESSION_KEY, 0);

        $throttleSeconds = (int) config('log.heartbeat_throttle_seconds', 60);

        if ($throttleSeconds < 10) {
            $throttleSeconds = 10;
        }

        if (($now - $lastHeartbeatAt) < $throttleSeconds) {
            return;
        }

        $request->session()->put(self::SESSION_KEY, $now);

        $this->adminSessionService->heartbeat($request);
    }
}
