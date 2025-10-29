<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthenticateWithJwtOrPassport
{
    /**
     * Try to authenticate the request by JWT (custom) or fallback to Passport (auth:api).
     * If a JWT is provided, it must contain only the 'uuid' claim for identification.
     */
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization') ?: $request->header('authorization');
        if ($authHeader && preg_match('/Bearer\s+(\S+)/', $authHeader, $m)) {
            $token = $m[1];
            // Try decode as our custom JWT first
            try {
                $secret = env('JWT_SECRET');
                if (empty($secret)) {
                    // if no secret configured, do not attempt JWT decode
                    throw new \RuntimeException('JWT secret not configured');
                }
                $decoded = JWT::decode($token, new Key($secret, 'HS256'));
                // Expecting uuid claim
                if (empty($decoded->uuid)) {
                    return response()->json(['message' => 'Token invalide: uuid manquant'], 401);
                }
                $uuid = (string) $decoded->uuid;
                $user = User::where('id', $uuid)->first();
                if (! $user) {
                    return response()->json(['message' => 'Utilisateur introuvable'], 401);
                }
                // Authenticate the user for the request lifecycle
                Auth::setUser($user);
                return $next($request);
            } catch (\Throwable $e) {
                // Fall back to passport verification below
            }
        }

        // Fallback: attempt to use Passport (auth:api)
        try {
            if (Auth::guard('api')->check()) {
                return $next($request);
            }
        } catch (\Throwable $_) {
            // If passport not configured or failed, respond unauthorized
        }

        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
