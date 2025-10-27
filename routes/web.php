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

// Route personnalisée pour forcer HTTPS sur la documentation Swagger
Route::get('/api-docs.json', function () {
    $path = storage_path('api-docs/api-docs.json');
    
    if (!file_exists($path)) {
        // Générer si n'existe pas
        \Artisan::call('l5-swagger:generate');
        
        if (!file_exists($path)) {
            abort(404, 'API documentation not found');
        }
    }
    
    // Lire et modifier les URLs en HTTPS
    $content = file_get_contents($path);
    $json = json_decode($content, true);
    
    // Remplacer toutes les URLs HTTP par HTTPS
    if (isset($json['servers'])) {
        foreach ($json['servers'] as &$server) {
            if (isset($server['url'])) {
                $server['url'] = str_replace('http://', 'https://', $server['url']);
            }
        }
    }
    
    // Forcer HTTPS dans host
    if (isset($json['host'])) {
        $json['host'] = 'gestioncompte-api.onrender.com';
    }
    
    if (isset($json['schemes'])) {
        $json['schemes'] = ['https'];
    }
    
    $content = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
    return response($content)
        ->header('Content-Type', 'application/json')
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Cache-Control', 'no-cache, must-revalidate');
});
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
