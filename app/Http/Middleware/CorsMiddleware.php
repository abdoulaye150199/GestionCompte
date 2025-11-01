<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * CORS middleware that handles all OPTIONS preflight requests and adds proper CORS headers.
 * This ensures all API endpoints accept CORS requests from allowed origins.
 */
class CorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Handle preflight OPTIONS requests
        if ($request->getMethod() === 'OPTIONS') {
            $allowedOrigins = [
                'http://127.0.0.1:8000',
                'http://localhost:3000',
                'https://gestioncompte-2.onrender.com',
                'https://gestioncompte-jmtc.onrender.com',
                'https://compte-api-transaction-abdoulaye-diallo.onrender.com',
            ];

            $origin = $request->headers->get('origin');
            $allowOrigin = in_array($origin, $allowedOrigins) ? $origin : '*';

            return response()->noContent(204, [
                'Access-Control-Allow-Origin' => $allowOrigin,
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With, X-CSRF-TOKEN, Accept, Authorization, X-Requested-With',
                'Access-Control-Allow-Credentials' => $allowOrigin === '*' ? 'false' : 'true',
                'Access-Control-Max-Age' => '86400',
            ]);
        }

        $response = $next($request);

        // Add CORS headers to actual responses
        $allowedOrigins = [
            'http://127.0.0.1:8000',
            'http://localhost:3000',
            'https://gestioncompte-2.onrender.com',
            'https://gestioncompte-jmtc.onrender.com',
            'https://compte-api-transaction-abdoulaye-diallo.onrender.com',
        ];

        $origin = $request->headers->get('origin');
        $allowOrigin = in_array($origin, $allowedOrigins) ? $origin : '*';

        $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With, X-CSRF-TOKEN, Accept, Authorization, X-Requested-With');
        $response->headers->set('Access-Control-Allow-Credentials', $allowOrigin === '*' ? 'false' : 'true');

        return $response;
    }
}