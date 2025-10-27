<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ResourceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $model = null): Response
    {
        // Check if the request has an ID parameter (for show, update, delete routes)
        $id = $request->route('id') ?? $request->route('user') ?? null;

        if ($id && $model) {
            // Determine the model class
            $modelClass = $this->getModelClass($model);

            if ($modelClass) {
                // Try to retrieve the resource from cache first
                $cacheKey = "{$model}_{$id}";
                $resource = Cache::get($cacheKey);

                if (!$resource) {
                    // If not in cache, fetch from database
                    $resource = $modelClass::find($id);

                    if ($resource) {
                        // Cache the resource for future requests (cache for 5 minutes)
                        Cache::put($cacheKey, $resource, 300);
                    }
                }

                // Attach the resource to the request for easy access in controllers
                $request->merge(['cached_resource' => $resource]);
            }
        }

        return $next($request);
    }

    /**
     * Get the model class based on the model name.
     */
    private function getModelClass(string $model): ?string
    {
        $models = [
            'user' => 'App\Models\User',
            'client' => 'App\Models\Client',
            'admin' => 'App\Models\Admin',
            'compte' => 'App\Models\Compte',
        ];

        return $models[$model] ?? null;
    }
}
