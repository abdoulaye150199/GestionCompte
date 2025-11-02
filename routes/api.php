<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\DashboardController;

Route::prefix('v1')->group(function () {

    // Auth
    Route::post('login', [LoginController::class, 'login']);
    Route::post('clients/change-password', [AuthController::class, 'changePassword'])->middleware('auth:api');

    // Comptes
    Route::get('comptes', [CompteController::class, 'index'])
        ->middleware(['auth:api', 'admin', \App\Http\Middleware\AccountAccessMiddleware::class]);

    Route::patch('comptes/{identifiant}', [CompteController::class, 'update'])
        ->middleware(['auth:api', 'admin', 'logging', \App\Http\Middleware\AccountAccessMiddleware::class]);

    Route::delete('comptes/{compteId}', [CompteController::class, 'destroy'])
        ->middleware(['auth:api', 'admin', 'logging', \App\Http\Middleware\AccountAccessMiddleware::class]);

    Route::get('comptes/{numero}', [CompteController::class, 'show'])
        ->middleware(['auth:api', 'admin', \App\Http\Middleware\AccountAccessMiddleware::class]);

    Route::post('accounts', [AccountController::class, 'store'])->middleware(['auth:api', 'admin', 'logging']);
    Route::post('comptes', [AccountController::class, 'store'])->middleware(['auth:api', 'admin', 'logging']);

    Route::post('messages', [MessageController::class, 'send'])->middleware('logging');

    // Users
    Route::get('users/clients', [UserController::class, 'clients'])->middleware(['auth:api', 'admin']);
    Route::get('users/admins', [UserController::class, 'admins'])->middleware(['auth:api', 'admin']);
    Route::get('users/client', [UserController::class, 'findClient'])->middleware(['auth:api', 'admin']);

    // Health check
    Route::get('health', [HealthController::class, 'index']);

    // Transactions
    Route::get('transactions', [TransactionController::class, 'index'])->middleware(['auth:api', 'admin']);
    Route::get('transactions/{id}', [TransactionController::class, 'show'])->middleware(['auth:api', 'admin']);
    Route::post('transactions', [TransactionController::class, 'store'])->middleware(['auth:api', 'admin']);
    Route::put('transactions/{id}', [TransactionController::class, 'update'])->middleware(['auth:api', 'admin']);
    Route::delete('transactions/{id}', [TransactionController::class, 'destroy'])->middleware('auth:api');

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'global'])->middleware('auth:api');
    Route::get('dashboard/me', [DashboardController::class, 'me'])->middleware('auth:api');

    // Comptes actions spécifiques
    Route::get('comptes/mes-comptes', [CompteController::class, 'mesComptes'])->middleware('auth:api');
    Route::post('comptes/{id}/archive', [CompteController::class, 'archive'])
        ->middleware(['auth:api', \App\Http\Middleware\AccountAccessMiddleware::class]);

    Route::post('comptes/{compte}/bloquer', [CompteController::class, 'bloquer'])
        ->middleware(['auth:api', \App\Http\Middleware\AccountAccessMiddleware::class]);
    Route::post('comptes/numero/{numero}/bloquer', [CompteController::class, 'bloquerByNumero'])
        ->middleware(['auth:api', \App\Http\Middleware\AccountAccessMiddleware::class]);
    Route::post('comptes/{compte}/bloquer-v2', [CompteController::class, 'bloquerV2'])
        ->middleware(['auth:api','logging', \App\Http\Middleware\AccountAccessMiddleware::class]);
    Route::post('comptes/{compte}/debloquer', [CompteController::class, 'debloquer'])
        ->middleware(['auth:api','logging', \App\Http\Middleware\AccountAccessMiddleware::class]);

    Route::get('comptes/{numeroCompte}', [CompteController::class, 'showByNumero'])
        ->middleware(['auth:api', \App\Http\Middleware\AccountAccessMiddleware::class]);

});
