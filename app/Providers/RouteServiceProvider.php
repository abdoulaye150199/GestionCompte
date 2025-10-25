<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Redirect some globally-registered package routes to the prefixed versions
        // so that externally visible URLs always include "abdoulaye.diallo".
        Route::permanentRedirect('api/documentation', 'abdoulaye.diallo/api/documentation');
        Route::permanentRedirect('api/oauth2-callback', 'abdoulaye.diallo/api/oauth2-callback');
        Route::permanentRedirect('docs', 'abdoulaye.diallo/docs');
        Route::permanentRedirect('docs/asset/{asset}', 'abdoulaye.diallo/docs/asset/{asset}');
    // Note: passport/sanctum routes are registered by packages. To make prefixed URLs
    // usable, add redirects from the prefixed paths to the package endpoints.
    Route::permanentRedirect('abdoulaye.diallo/oauth/{any}', 'oauth/{any}')->where('any', '.*');
    Route::permanentRedirect('abdoulaye.diallo/sanctum/{any}', 'sanctum/{any}')->where('any', '.*');
    // Also ensure prefixed docs and api doc paths resolve to package endpoints if needed
    Route::permanentRedirect('abdoulaye.diallo/docs', 'docs');
    Route::permanentRedirect('abdoulaye.diallo/docs/asset/{asset}', 'docs/asset/{asset}');

        $this->routes(function () {
            // Prefix all routes with the user's firstname.lastname as requested
            // Resulting paths will look like: /abdoulaye.diallo/api/... and /abdoulaye.diallo/...
            Route::middleware('api')
                ->prefix('abdoulaye.diallo/api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->prefix('abdoulaye.diallo')
                ->group(base_path('routes/web.php'));
        });
    }
}
