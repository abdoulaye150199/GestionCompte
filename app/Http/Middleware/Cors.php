<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        if (method_exists($response, 'header')) {
            $response->header('Access-Control-Allow-Origin', '*')
                    ->header('Access-Control-Allow-Methods', '*')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, X-Auth-Token, Origin, Authorization, X-Requested-With, X-CSRF-TOKEN')
                    ->header('Access-Control-Allow-Credentials', 'true')
                    ->header('Access-Control-Max-Age', '86400');
        }

        return $response;
    }
}