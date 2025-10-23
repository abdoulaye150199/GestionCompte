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

Route::middleware('auth:api')->group(function () {
    // Routes pour les clients
    Route::apiResource('clients', \App\Http\Controllers\ClientController::class);
    
    // Routes pour les comptes bancaires
    Route::apiResource('bank-accounts', \App\Http\Controllers\BankAccountController::class);
    
    // Routes pour les opérations bancaires
    Route::post('bank-accounts/{bankAccount}/deposit', [\App\Http\Controllers\BankAccountController::class, 'deposit']);
    Route::post('bank-accounts/{bankAccount}/withdraw', [\App\Http\Controllers\BankAccountController::class, 'withdraw']);
    
    // Route pour l'utilisateur authentifié
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});