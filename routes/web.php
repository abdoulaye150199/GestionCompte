<?php

use Illuminate\Support\Facades\Route;

// NOTE: v1 API routes are mounted in routes/api.php so they use the `api` middleware
// (stateless, no CSRF). We used to include them here for a custom local prefix but
// serving API routes from `routes/api.php` is the recommended approach.


Route::get('/', function () {
    return view('welcome');
});

// Documentation Swagger UI (static)
Route::get('/docs', function () {
    $path = public_path('docs/index.html');
    if (file_exists($path)) {
        return response()->file($path);
    }
    abort(404);
});

// Also serve the docs under the user's prefixed path: /abdoulaye.diallo/docs
Route::get('/abdoulaye.diallo/docs', function () {
    $path = public_path('docs/index.html');
    if (file_exists($path)) {
        return response()->file($path);
    }
    abort(404);
});

// Serve static assets for the prefixed docs path
Route::get('/abdoulaye.diallo/docs/asset/{asset}', function ($asset) {
    $path = public_path('docs/asset/' . $asset);
    if (file_exists($path)) {
        return response()->file($path);
    }
    abort(404);
})->where('asset', '.*');

// Temporary protected diagnostic endpoint (requires DIAG_SECRET or APP_DEBUG=true)
Route::get('/abdoulaye.diallo/_diagnose', [\App\Http\Controllers\DiagController::class, 'index']);
