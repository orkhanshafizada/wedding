<?php

namespace App\Exceptions;

use App\Services\ApiResponseService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->renderable(function (Throwable $e, Request $request) {
            if (!$this->isApiRequest($request)) {
                return null;
            }

            $api = app(ApiResponseService::class);

            if ($e instanceof ValidationException) {
                Log::warning('API ValidationException', [
                    'path' => $request->path(),
                    'method' => $request->method(),
                    'errors' => $e->errors(),
                ]);

                return $api->validation($e->errors());
            }

            if ($e instanceof AuthenticationException) {
                Log::warning('API AuthenticationException', [
                    'path' => $request->path(),
                    'method' => $request->method(),
                ]);

                return $api->unauthorized();
            }

            if ($e instanceof AuthorizationException) {
                Log::warning('API AuthorizationException', [
                    'path' => $request->path(),
                    'method' => $request->method(),
                ]);

                return $api->forbidden();
            }

            if ($e instanceof ModelNotFoundException) {
                Log::warning('API ModelNotFoundException', [
                    'path' => $request->path(),
                    'method' => $request->method(),
                ]);

                return $api->notFound();
            }

            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();

                Log::warning('API HttpException', [
                    'status' => $status,
                    'message' => $e->getMessage(),
                    'path' => $request->path(),
                    'method' => $request->method(),
                ]);

                $message = match ($status) {
                    404 => __('Not Found'),
                    405 => __('Method Not Allowed'),
                    419 => __('Page Expired'),
                    429 => __('Too Many Requests'),
                    default => $e->getMessage() !== '' ? $e->getMessage() : __('Error'),
                };

                return $api->fail($message, $status);
            }

            Log::error('API Exception', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'path' => $request->path(),
                'method' => $request->method(),
            ]);

            $meta = [];

            if (config('app.debug')) {
                $meta = [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ];
            }

            return $api->serverError('Server Error', [], $meta);
        });
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }
}
