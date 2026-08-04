<?php

namespace App\Http\Middleware;

use App\Support\Settings;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class SecurityRateLimiter
{
    public function handle(Request $request, Closure $next, string $profile = 'api'): Response
    {
        if (! $this->isEnabled()) {
            return $next($request);
        }

        [$maxAttempts, $decayMinutes] = $this->resolveLimits($profile);

        if ($maxAttempts < 1 || $decayMinutes < 1) {
            return $next($request);
        }

        $key = $this->resolveKey($request, $profile);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return $this->buildTooManyAttemptsResponse($key, $maxAttempts);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        $response->headers->set('X-RateLimit-Limit', (string) $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', (string) RateLimiter::retriesLeft($key, $maxAttempts));

        return $response;
    }

    private function isEnabled(): bool
    {
        $rateLimit = Settings::get('security', 'rate_limit', [
            'enabled' => true,
            'max' => 60,
            'window_min' => 1,
        ]);

        return (bool) ($rateLimit['enabled'] ?? true);
    }

    private function resolveLimits(string $profile): array
    {
        if ($profile === 'auth') {
            return [
                max(1, (int) Settings::get('security', 'max_login_attempts', 5)),
                max(1, (int) Settings::get('security', 'lock_minutes', 15)),
            ];
        }

        $rateLimit = Settings::get('security', 'rate_limit', [
            'enabled' => true,
            'max' => 60,
            'window_min' => 1,
        ]);

        return [
            max(1, (int) ($rateLimit['max'] ?? 60)),
            max(1, (int) ($rateLimit['window_min'] ?? 1)),
        ];
    }

    private function resolveKey(Request $request, string $profile): string
    {
        $routeName = (string) ($request->route()?->getName() ?? $request->path());

        $identifier = $profile === 'auth'
            ? $this->resolveAuthIdentifier($request)
            : $this->resolveRequestIdentifier($request);

        return sha1(implode('|', [
            'security-rate-limit',
            $profile,
            $routeName,
            $request->method(),
            $identifier,
        ]));
    }

    private function resolveRequestIdentifier(Request $request): string
    {
        $user = $request->user();

        if ($user !== null && $user->getAuthIdentifier() !== null) {
            return 'user:' . $user->getAuthIdentifier();
        }

        return 'ip:' . (string) $request->ip();
    }

    private function resolveAuthIdentifier(Request $request): string
    {
        $credential = strtolower(trim((string) (
        $request->input('email')
            ?: $request->input('phone')
            ?: $request->input('username')
                ?: ''
        )));

        if ($credential !== '') {
            return 'credential:' . $credential . '|ip:' . (string) $request->ip();
        }

        return $this->resolveRequestIdentifier($request);
    }

    private function buildTooManyAttemptsResponse(string $key, int $maxAttempts): JsonResponse
    {
        $retryAfter = RateLimiter::availableIn($key);

        return response()
            ->json([
                'message' => __('Too many attempts. Please try again later.'),
                'retry_after' => $retryAfter,
            ], Response::HTTP_TOO_MANY_REQUESTS)
            ->withHeaders([
                'Retry-After' => (string) $retryAfter,
                'X-RateLimit-Limit' => (string) $maxAttempts,
                'X-RateLimit-Remaining' => '0',
            ]);
    }
}
