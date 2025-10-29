<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Compte;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to restrict access to Compte resources.
 *
 * - Admin users are allowed to perform any action.
 * - Client users may only operate on their own resources (matched by user_id or client->user_id).
 * - For collection GET (/comptes) the middleware sets a request attribute so the controller
 *   can restrict the listing to the authenticated user's accounts.
 */
class AccountAccessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // If the user is an admin (presence of admin relation), allow everything.
        try {
            // Prefer an instanceof check so static analyzers know the $user type.
            if ($user instanceof \App\Models\User && $user->admin()->exists()) {
                return $next($request);
            }
        } catch (\Throwable $e) {
            // If anything goes wrong while checking admin, deny with 403
            return response()->json(['message' => 'Access denied.'], 403);
        }

        // Not an admin -> Restrict to own resources.
        // Determine if this request targets a specific compte resource.
        $paramKeys = ['compteId', 'identifiant', 'compte', 'numero', 'numeroCompte', 'id', 'identifier'];
        $param = null;
        foreach ($paramKeys as $k) {
            if ($request->route() && $request->route($k) !== null) {
                $param = $request->route($k);
                break;
            }
            // also check request->route parameters array
            $routeParams = $request->route() ? $request->route()->parameters() : [];
            if (isset($routeParams[$k])) {
                $param = $routeParams[$k];
                break;
            }
        }

        // If a specific compte identifier exists in route, validate ownership.
        if ($param) {
            $compte = null;
            $isUuid = (bool) preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $param);
            if ($isUuid) {
                $compte = Compte::find($param);
            }
            if (! $compte) {
                $compte = Compte::where('numero_compte', $param)->first();
            }

            if (! $compte) {
                return response()->json(['message' => 'Compte introuvable'], 404);
            }

            $userId = (string) $user->id;
            $owns = false;
            // direct owner via user_id
            if (! empty($compte->user_id) && (string) $compte->user_id === $userId) {
                $owns = true;
            }
            // owner via client->user_id
            if (! $owns && $compte->client && isset($compte->client->user_id) && (string) $compte->client->user_id === $userId) {
                $owns = true;
            }

            if (! $owns) {
                return response()->json(['message' => 'Vous n\'êtes pas autorisé à accéder à cette ressource.'], 403);
            }

            // Ownership verified
            return $next($request);
        }

        // No specific compte param: if it's a GET on collection /comptes, instruct controller to restrict
        // the listing to the authenticated user's accounts by setting a request attribute.
        $isGetCollection = $request->isMethod('GET') && preg_match('#/comptes$#', $request->path());
        if ($isGetCollection) {
            $request->attributes->set('restrict_to_user_id', (string) $user->id);
            return $next($request);
        }

        // For other requests that don't target a specific compte, allow (or controllers will enforce further checks)
        return $next($request);
    }
}
