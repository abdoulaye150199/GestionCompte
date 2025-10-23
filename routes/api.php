<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('documentation')->group(function () {
    Route::get('/', [L5Swagger\Http\Controllers\SwaggerController::class, 'api'])->name('l5swagger.api');
    Route::get('/doc', [L5Swagger\Http\Controllers\SwaggerController::class, 'docs'])->name('l5swagger.docs');
    Route::get('/asset/{asset}', [L5Swagger\Http\Controllers\SwaggerAssetController::class, 'index'])->name('l5swagger.asset');
});

// API V1 Routes
Route::group(['prefix' => 'v1'], function () {
    // Route principale pour les comptes
    Route::get('/comptes', [\App\Http\Controllers\API\BankAccountController::class, 'index'])->name('comptes.index');
    
    // Route alternative au cas où
    Route::get('/accounts', [\App\Http\Controllers\API\BankAccountController::class, 'index'])->name('accounts.index');
    
    // Route sans préfixe v1 (fallback)
    Route::get('/', [\App\Http\Controllers\API\BankAccountController::class, 'index'])->name('api.index');
});

// Route de test pour vérifier que l'API fonctionne
Route::get('/health-check', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is working correctly',
        'timestamp' => now()->toISOString(),
        'environment' => app()->environment()
    ]);
});

// Routes d'authentification
Route::post('v1/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::post('v1/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware('auth:api');

Route::middleware('auth:api')->group(function () {
    // Routes pour les clients
    Route::apiResource('clients', \App\Http\Controllers\ClientController::class);

    // Routes pour les comptes bancaires
    Route::prefix('v1')->group(function () {
        Route::get('accounts', [\App\Http\Controllers\API\BankAccountController::class, 'index']);
        Route::get('accounts/{id}', [\App\Http\Controllers\API\BankAccountController::class, 'show']);
    });

    // Route pour l'utilisateur authentifié
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Routes publiques sans authentification
Route::prefix('v1')->group(function () {
    Route::get('/comptes', [\App\Http\Controllers\API\BankAccountController::class, 'index'])->name('comptes.index');
});