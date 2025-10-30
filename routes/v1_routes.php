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
    // Public routes (no auth required)
    // Public routes: only login endpoints (everything else requires a token)
    Route::post('login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login']);
    // debug route that bypasses FormRequest validation
    Route::post('login-raw', [\App\Http\Controllers\Api\V1\AuthController::class, 'loginRaw']);

    // Protected routes (require authentication) - ALL routes except login/login-raw
    Route::middleware('auth:api')->group(function () {
        // Demo endpoint (now protected)
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

        // Account creation and health check (moved into protected group)
        Route::post('accounts', [AccountController::class, 'store'])->middleware('logging');
        Route::get('health', [\App\Http\Controllers\HealthController::class, 'index']);
        // Auth actions
        Route::post('logout', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout']);
        Route::get('user', [\App\Http\Controllers\Api\V1\AuthController::class, 'user']);

        // Comptes - viewing and management
        Route::get('comptes', [CompteController::class, 'index']);
        Route::get('comptes/{identifier}', [CompteController::class, 'show']);
        Route::get('comptes/mes-comptes', [CompteController::class, 'mesComptes']);
        Route::post('comptes/{id}/archive', [CompteController::class, 'archive']);

        // Modification endpoints (logging middleware preserved)
        Route::patch('comptes/{identifiant}', [CompteController::class, 'update'])->middleware('logging');
        Route::delete('comptes/{compteId}', [CompteController::class, 'destroy'])->middleware('logging');

        // Blocking/unblocking endpoints
        Route::post('comptes/{compte}/bloquer', [CompteController::class, 'bloquer']);
        Route::post('comptes/numero/{numero}/bloquer', [CompteController::class, 'bloquerByNumero']);
        Route::post('comptes/{compte}/bloquer-v2', [CompteController::class, 'bloquerV2'])->middleware('logging');
        Route::post('comptes/{compte}/debloquer', [CompteController::class, 'debloquer'])->middleware('logging');

        // Account and user management
        Route::post('messages', [\App\Http\Controllers\MessageController::class, 'send'])->middleware('logging');
        Route::get('users/clients', [UserController::class, 'clients']);
        Route::get('users/admins', [UserController::class, 'admins']);

        // Endpoint: récupérer un compte par numéro (protected)
        Route::get('comptes/{numeroCompte}', [CompteController::class, 'showByNumero']);
    });
});
