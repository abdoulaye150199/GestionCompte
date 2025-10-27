<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\RestResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WelcomeController extends Controller
{
    use RestResponse;

    /**
     * Return a welcome message with logging
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Log request metadata
        $method = $request->method();
        $path = $request->path();
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        Log::info("Request received: {$method} {$path}", [
            'method' => $method,
            'path' => $path,
            'ip' => $ip,
            'user_agent' => $userAgent,
        ]);

        // Return welcome message
        return $this->successResponse(
            null,
            'Welcome to the Laravel API Service!'
        );
    }
}
