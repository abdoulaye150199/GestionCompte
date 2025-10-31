<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('api')->check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = Auth::guard('api')->user();
        
        // Check if admin record exists for this user
        $adminExists = \Illuminate\Support\Facades\DB::table('admins')
            ->where('user_id', $user->id)
            ->exists();

        if (!$adminExists) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        return $next($request);
    }
}