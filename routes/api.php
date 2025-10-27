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

// API Version 1
Route::prefix('v1')->middleware(['auth:api', 'api.rating'])->group(function () {

    // Routes des comptes bancaires
    // Routes spécifiques doivent être définies avant apiResource pour éviter les conflits
    Route::get('comptes/archives', [CompteController::class, 'getArchivedComptes']);
    Route::get('comptes/bloques', [CompteController::class, 'getBloquedComptes']);
    Route::post('comptes/{id}/restaurer', [CompteController::class, 'restoreArchivedCompte']);
    Route::post('comptes/{compteId}/bloquer', [CompteController::class, 'bloquer']);
    Route::post('comptes/{compteId}/debloquer', [CompteController::class, 'debloquer']);
    Route::apiResource('comptes', CompteController::class);

    // Routes des utilisateurs
    Route::middleware('resource:user')->group(function () {
        Route::apiResource('users', UserController::class);
    });
});

// Routes d'authentification Passport
Route::prefix('v1')->group(function () {
    Route::post('login', [App\Http\Controllers\Api\V1\AuthController::class, 'login']);
    Route::post('register', [App\Http\Controllers\Api\V1\AuthController::class, 'register']);
    Route::middleware('auth:api')->post('logout', [App\Http\Controllers\Api\V1\AuthController::class, 'logout']);
    Route::get('welcome', [App\Http\Controllers\Api\V1\WelcomeController::class, 'index']);
});

// Route par défaut de Laravel (peut être supprimée si non nécessaire)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
