<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;

abstract class BaseApiController extends Controller
{
    protected function api(): ApiResponseService
    {
        return app(ApiResponseService::class);
    }

    protected function response(mixed $data = [], string $message = 'OK', int $status = 200, array $meta = [], array $links = []): JsonResponse
    {
        return $this->api()->response($data, $message, $status, $meta, $links);
    }

    protected function created(mixed $data = [], string $message = 'Created', array $meta = [], array $links = []): JsonResponse
    {
        return $this->api()->created($data, $message, $meta, $links);
    }

    protected function noContent(): JsonResponse
    {
        return $this->api()->noContent();
    }

    protected function fail(string $message = 'Error', int $status = 400, array $errors = [], array $meta = []): JsonResponse
    {
        return $this->api()->fail($message, $status, $errors, $meta);
    }

    protected function paginated($paginator, mixed $data, string $message = 'OK', int $status = 200, array $extraMeta = []): JsonResponse
    {
        return $this->api()->paginated($paginator, $data, $message, $status, $extraMeta);
    }
}
