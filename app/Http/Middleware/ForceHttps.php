<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceHttps
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->secure() && app()->environment('production')) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        if (env('FORCE_HTTPS', true)) {
            $request->server->set('HTTPS', 'on');
            $request->server->set('SERVER_PORT', 443);
            $request->server->set('HTTP_X_FORWARDED_PROTO', 'https');
        }

        return $next($request);
    }
}