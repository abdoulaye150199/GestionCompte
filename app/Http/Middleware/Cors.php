<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Simple CORS middleware that echoes the request Origin and allows common headers.
 * This complements Laravel's HandleCors and ensures Swagger UI requests succeed.
 */
class Cors
{
    public function handle(Request $request, Closure $next)
    {
    // Allow all origins
    $origin = '*';

        // Preflight
        if ($request->getMethod() === 'OPTIONS') {
            $headers = [
                'Access-Control-Allow-Origin' => $origin,
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With, X-CSRF-TOKEN, Accept, Authorization',
                // When using a wildcard origin, credentials are not allowed by browsers.
                'Access-Control-Allow-Credentials' => 'false',
                'Access-Control-Max-Age' => '86400',
            ];

            return response()->noContent(204, $headers);
        }

        $response = $next($request);

        if ($response instanceof Response) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With, X-CSRF-TOKEN, Accept, Authorization');
            // Wildcard origin; do not allow credentials.
            $response->headers->set('Access-Control-Allow-Credentials', 'false');
        }

        return $response;
    }
}