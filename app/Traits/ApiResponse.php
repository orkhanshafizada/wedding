<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

trait ApiResponse
{
    protected function success(array|JsonResource $data = [], string $message = 'OK', int $status = 200, array $meta = []): JsonResponse
    {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $data instanceof JsonResource ? $data->resolve() : $data,
        ];

        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    protected function paginated(Paginator $paginator, array|JsonResource $data = [], string $message = 'OK', int $status = 200, array $extraMeta = []): JsonResponse
    {
        $meta = array_merge([
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'has_more' => $paginator->hasMorePages(),
        ], $extraMeta);

        return $this->success($data instanceof JsonResource ? $data->resolve() : $data, $message, $status, $meta);
    }

    protected function fail(string $message = 'Error', int $status = 400, array $errors = [], array $meta = []): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $payload['errors'] = $this->formatErrors($errors);
        }

        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    protected function formatErrors(array $errors): array
    {
        $normalized = [];

        foreach ($errors as $key => $value) {
            if (is_array($value)) {
                $normalized[$key] = array_values(Arr::flatten($value));
            } else {
                $normalized[$key] = [(string) $value];
            }
        }

        return $normalized;
    }

    protected function created(array|JsonResource $data = [], string $message = 'Created'): JsonResponse
    {
        return $this->success($data instanceof JsonResource ? $data->resolve() : $data, $message, 201);
    }

    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    protected function unauthorized(string $message = 'Unauthorized', array $errors = []): JsonResponse
    {
        return $this->fail($message, 401, $errors);
    }

    protected function forbidden(string $message = 'Forbidden', array $errors = []): JsonResponse
    {
        return $this->fail($message, 403, $errors);
    }

    protected function notFound(string $message = 'Not Found', array $errors = []): JsonResponse
    {
        return $this->fail($message, 404, $errors);
    }

    protected function validationFailed(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->fail($message, 422, $errors);
    }

    protected function serverError(string $message = 'Server Error', array $errors = []): JsonResponse
    {
        return $this->fail($message, 500, $errors);
    }
}
