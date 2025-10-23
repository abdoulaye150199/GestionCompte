<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route de test pour vérifier que l'API fonctionne
Route::get('/health-check', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is working correctly',
        'timestamp' => now()->toISOString(),
        'environment' => app()->environment()
    ]);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});