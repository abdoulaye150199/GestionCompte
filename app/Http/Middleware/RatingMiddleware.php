<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RatingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Vérifier si l'utilisateur a atteint une limite de rating
        $this->checkRatingLimit($request, $response);

        return $response;
    }

    /**
     * Vérifier et enregistrer les utilisateurs qui atteignent une limite de rating
     */
    private function checkRatingLimit(Request $request, Response $response): void
    {
        // Récupérer les en-têtes de limitation de débit
        $remaining = $request->header('X-RateLimit-Remaining');
        $limit = $request->header('X-RateLimit-Limit');

        // Si les en-têtes sont présents et que la limite est atteinte
        if ($remaining !== null && $limit !== null && (int)$remaining === 0) {
            $user = $request->user();

            if ($user) {
                // Enregistrer l'événement de limite atteinte
                Log::info('Rate limit reached', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'endpoint' => $request->path(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'timestamp' => now()->toISOString(),
                ]);

                // Ici, vous pourriez également :
                // - Envoyer une notification à l'utilisateur
                // - Stocker dans une table dédiée
                // - Déclencher des actions administratives
            }
        }
    }
}
