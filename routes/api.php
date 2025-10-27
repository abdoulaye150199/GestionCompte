<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CompteController;
use App\Http\Controllers\Api\V1\UserController;

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

// Routes publiques (sans authentification)
Route::prefix('v1')->group(function () {
    Route::post('/login', [App\Http\Controllers\Api\V1\AuthController::class, 'login']);
    Route::post('/register', [App\Http\Controllers\Api\V1\AuthController::class, 'register']);
    Route::get('/welcome', [App\Http\Controllers\Api\V1\WelcomeController::class, 'index']);

    // Routes protégées par Passport
    Route::middleware(['auth:api', 'api.rating'])->group(function () {
        // Auth
        Route::post('/logout', [App\Http\Controllers\Api\V1\AuthController::class, 'logout']);
        Route::get('/user', [App\Http\Controllers\Api\V1\AuthController::class, 'user']);

        // Routes des comptes bancaires
        Route::prefix('comptes')->group(function () {
            Route::get('archives', [CompteController::class, 'getArchivedComptes']);
            Route::get('bloques', [CompteController::class, 'getBloquedComptes']);
            Route::post('{id}/restaurer', [CompteController::class, 'restoreArchivedCompte']);
            Route::post('{compteId}/bloquer', [CompteController::class, 'bloquer']);
            Route::post('{compteId}/debloquer', [CompteController::class, 'debloquer']);
        });
        Route::apiResource('comptes', CompteController::class);

        // Routes des utilisateurs
        Route::middleware('resource:user')->group(function () {
            Route::apiResource('users', UserController::class);
        });
    });
});
