<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\AccountController;

// Routes pour /api/v1/... (définies ici pour réutilisation dans api.php et web.php)

// Wrap v1 routes with CORS middleware to allow cross-origin requests (useful for Swagger UI and external docs)
// NOTE: this file defines routes relative to the mount point. When included
// under a prefix like `api/v1` or `khouss.ngom/api/v1` the final URIs will be
// constructed correctly. Do NOT hardcode `/v1` or `/api/v1` here.
Route::middleware('cors')->group(function () {
    // Demo endpoint
    Route::get('comptes-demo', function () {
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'numero' => 'CPT-0001',
                    'solde' => '1000.00',
                    'type' => 'courant'
                ]
            ]
        ]);
    });

    // Public endpoints that don't require authentication
    Route::get('comptes', [CompteController::class, 'index']);
    Route::get('comptes/{identifier}', [CompteController::class, 'show']);
    Route::post('accounts', [AccountController::class, 'store'])->middleware('logging');

    // Protected endpoints requiring authentication
    Route::middleware('auth:api')->group(function () {
        // Account management
        Route::patch('comptes/{identifiant}', [CompteController::class, 'update'])->middleware('logging');
        Route::delete('comptes/{compteId}', [CompteController::class, 'destroy'])->middleware('logging');

        // Messaging
        Route::post('messages', [\App\Http\Controllers\MessageController::class, 'send'])->middleware('logging');
    });

    Route::get('users/clients', [UserController::class, 'clients']);
    Route::get('users/admins', [UserController::class, 'admins']);

    Route::get('health', [\App\Http\Controllers\HealthController::class, 'index']);

    Route::get('comptes/mes-comptes', [CompteController::class, 'mesComptes']);
    Route::post('comptes/{id}/archive', [CompteController::class, 'archive']);

    // Blocage endpoints
    Route::post('comptes/{compte}/bloquer', [CompteController::class, 'bloquer']);
    Route::post('comptes/numero/{numero}/bloquer', [CompteController::class, 'bloquerByNumero']);
    // New payload-based blocking/unblocking endpoints
    Route::post('comptes/{compte}/bloquer-v2', [CompteController::class, 'bloquerV2'])->middleware('logging');
    Route::post('comptes/{compte}/debloquer', [CompteController::class, 'debloquer'])->middleware('logging');

    // Endpoint: récupérer un compte par numéro
    Route::get('comptes/{numeroCompte}', [CompteController::class, 'showByNumero']);
});
