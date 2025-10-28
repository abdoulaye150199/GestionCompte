<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        // Only add CORS headers if the response object supports header() and
        // if the request Origin is allowed by the app config.
        if (method_exists($response, 'header')) {
            $origin = $request->headers->get('Origin');
            $allowed = config('cors.allowed_origins', []);

            // If the origin is allowed, echo it back; otherwise do not set
            // credentials headers to avoid CORS failures in the browser.
            if ($origin && in_array($origin, $allowed, true)) {
                $response->header('Access-Control-Allow-Origin', $origin);
                $response->header('Access-Control-Allow-Credentials', config('cors.supports_credentials') ? 'true' : 'false');
            } else {
                // Fallback: no wildcard when credentials are enabled
                if (! config('cors.supports_credentials')) {
                    $response->header('Access-Control-Allow-Origin', '*');
                }
            }

            $response->header('Access-Control-Allow-Methods', implode(', ', (array) config('cors.allowed_methods', ['GET','POST','PUT','DELETE','OPTIONS'])));
            $response->header('Access-Control-Allow-Headers', implode(', ', (array) config('cors.allowed_headers', ['Content-Type', 'Authorization'])));
            $response->header('Access-Control-Max-Age', (string) config('cors.max_age', 86400));
        }

        return $response;
    }
}