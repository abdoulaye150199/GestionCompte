<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Documentation Routes
|--------------------------------------------------------------------------
|
| Routes for API documentation
|
*/

// Documentation Swagger
Route::get('documentation', [\L5Swagger\Http\Controllers\SwaggerController::class, 'api']);
Route::get('documentation/asset/{asset}', [
    \L5Swagger\Http\Controllers\SwaggerAssetController::class, 'index'
]);

Route::get('/health-check', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is working correctly',
        'timestamp' => now()->toISOString(),
        'environment' => app()->environment()
    ]);
});