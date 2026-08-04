<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class ApiResponseService
{
    public function response(mixed $data = [], string $message = 'OK', int $status = 200, array $meta = [], array $links = []): JsonResponse
    {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $this->normalizeData($data),
        ];

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        if ($links !== []) {
            $payload['links'] = $links;
        }

        return response()->json($payload, $status);
    }

    public function created(mixed $data = [], string $message = 'Created', array $meta = [], array $links = []): JsonResponse
    {
        return $this->response($data, $message, 201, $meta, $links);
    }

    public function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    public function fail(string $message = 'Error', int $status = 400, array $errors = [], array $meta = []): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== []) {
            $payload['errors'] = $this->normalizeErrors($errors);
        }

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    public function validation(array $errors, string $message = 'Validation failed', array $meta = []): JsonResponse
    {
        return $this->fail(__($message), 422, $errors, $meta);
    }

    public function unauthorized(string $message = 'Unauthorized', array $errors = []): JsonResponse
    {
        return $this->fail(__($message), 401, $errors);
    }

    public function forbidden(string $message = 'Forbidden', array $errors = []): JsonResponse
    {
        return $this->fail(__($message), 403, $errors);
    }

    public function notFound(string $message = 'Not Found', array $errors = []): JsonResponse
    {
        return $this->fail(__($message), 404, $errors);
    }

    public function serverError(string $message = 'Server Error', array $errors = [], array $meta = []): JsonResponse
    {
        return $this->fail(__($message), 500, $errors, $meta);
    }

    public function paginated(LengthAwarePaginator $paginator, mixed $data, string $message = 'OK', int $status = 200, array $extraMeta = []): JsonResponse
    {
        $meta = array_merge([
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'has_more' => $paginator->hasMorePages(),
        ], $extraMeta);

        $links = [
            'first' => $paginator->url(1),
            'last' => $paginator->url($paginator->lastPage()),
            'prev' => $paginator->previousPageUrl(),
            'next' => $paginator->nextPageUrl(),
        ];

        return $this->response($data, $message, $status, $meta, $links);
    }

    private function normalizeData(mixed $data): mixed
    {
        if ($data instanceof JsonResource) {
            $request = request() instanceof Request ? request() : null;
            return $data->toArray($request);
        }

        return $data ?? [];
    }

    private function normalizeErrors(array $errors): array
    {
        $normalized = [];

        foreach ($errors as $key => $value) {
            if (is_array($value)) {
                $normalized[$key] = array_values(array_map('strval', Arr::flatten($value)));
                continue;
            }

            $normalized[$key] = [(string) $value];
        }

        return $normalized;
    }
}
