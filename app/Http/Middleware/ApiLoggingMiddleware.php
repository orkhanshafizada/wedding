<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ApiLoggingMiddleware
{
    protected array $excludedPaths = [
        'api/health-check',
        'api/ping',
        'api/metrics',
    ];

    protected array $sensitiveData = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'access_token',
        'refresh_token',
        'api_key',
        'secret',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $response = $next($request);

        $duration = round((microtime(true) - $start) * 1000, 2);

        logger()->info('API_CALL', [
            'path' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'duration_ms' => $duration,
            'request' => [
                'headers' => $this->filterHeaders($request->headers->all()),
                'query' => $request->query(),
                'body' => $this->filterSensitive($request->all()),
            ],
            'response' => [
                'status' => $response->getStatusCode(),
                'content_type' => $response->headers->get('Content-Type'),
            ],
        ]);

        return $response;
    }

    protected function shouldSkip(Request $request): bool
    {
        foreach ($this->excludedPaths as $path) {
            if ($request->is($path)) return true;
        }
        return false;
    }

    protected function filterSensitive(array $data): array
    {
        foreach ($data as $k => $v) {
            if (in_array($k, $this->sensitiveData, true)) {
                $data[$k] = '[FILTERED]';
            } elseif (is_array($v)) {
                $data[$k] = $this->filterSensitive($v);
            }
        }
        return $data;
    }

    protected function filterHeaders(array $headers): array
    {
        $sensitive = ['authorization', 'cookie', 'x-xsrf-token'];

        foreach ($headers as $k => $v) {
            if (in_array(Str::lower($k), $sensitive, true)) {
                $headers[$k] = ['[FILTERED]'];
            }
        }
        return $headers;
    }
}
