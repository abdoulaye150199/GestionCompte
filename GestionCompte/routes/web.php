<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Documentation Swagger
Route::get('api/documentation', [\L5Swagger\Http\Controllers\SwaggerController::class, 'api']);
Route::get('api/documentation/asset/{asset}', [
    \L5Swagger\Http\Controllers\SwaggerAssetController::class, 'index'
]);
