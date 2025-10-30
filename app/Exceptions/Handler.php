<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // If request expects JSON (API), return a structured JSON response with status code and message
        if ($request->expectsJson() || str_starts_with($request->getRequestUri(), '/api')) {
            // Special-case validation exceptions to return the full errors array instead of a summarized message
            if ($e instanceof ValidationException) {
                $status = $e->status ?? 422;
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $e->errors(),
                ], $status);
            }

            $status = 500;
            if (method_exists($e, 'getStatusCode')) {
                $status = $e->getStatusCode();
            }

            $response = [
                'success' => false,
                'message' => $e->getMessage() ?: 'Server Error',
            ];

            if (config('app.debug')) {
                $response['exception'] = get_class($e);
                $response['trace'] = $e->getTrace();
            }

            return new JsonResponse($response, $status);
        }

        return parent::render($request, $e);
    }
}
