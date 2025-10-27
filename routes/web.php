<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Swagger documentation routes
Route::get('/api/documentation', function () {
    return view('vendor.l5-swagger.index', [
        'documentation' => 'default',
        'urlToDocs' => url('api-docs.json'),
        'useAbsolutePath' => true,
        'operationsSorter' => null,
        'configUrl' => null,
        'validatorUrl' => null,
    ]);
})->name('custom.l5-swagger.default.api');

Route::get('/docs', function () {
    return response()->file(storage_path('api-docs/api-docs.json'));
})->name('custom.l5-swagger.default.docs');

Route::get('/swagger-assets/{asset}', function ($asset) {
    return response()->file(public_path('swagger-assets/' . $asset));
})->where('asset', '.*')->name('custom.l5-swagger.default.asset');
