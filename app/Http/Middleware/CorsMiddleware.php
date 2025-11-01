<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Simple CORS middleware that echoes the request Origin and allows common headers.
 * This complements Laravel's HandleCors and ensures Swagger UI requests succeed.
 */
class CorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Normalize Origin header: some clients (native apps, file://, capacitor, etc.)
        // send origins with non-http schemes which Symfony's CORS handling rejects
        // with "URL scheme must be 'http' or 'https'". Clear such origins so
        // the framework's CORS middleware doesn't throw.
        $incomingOrigin = $request->headers->get('origin');

        if ($incomingOrigin) {
            $scheme = parse_url($incomingOrigin, PHP_URL_SCHEME);
            if (! in_array($scheme, ['http', 'https'], true)) {
                // Remove/sanitize origin header so HandleCors won't validate it.
                $request->headers->remove('origin');
                $incomingOrigin = null;
            }
        }

        // Allow all origins by default for responses (when origin is absent we
        // use '*'). If an incomingOrigin exists we echo it; otherwise use '*'.
        $origin = $incomingOrigin ?? '*';

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