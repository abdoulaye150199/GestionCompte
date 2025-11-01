<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\AccountController;


// Register API v1 routes.
// The earlier code accidentally wrapped the group in a Route::group() call
// that passed a Closure as the first argument (causing a TypeError). Keep
// a single, correct prefix group.
Route::prefix('v1')->group(function () {
    require __DIR__ . '/v1_routes.php';
});


