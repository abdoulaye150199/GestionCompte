<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Annotations as OA;

/**
 * OpenAPI server definitions (production and development)
 *
 * @OA\Info(
 *     version="1.0.0",
 *     title="Gestion Compte API",
 *     description="API de gestion des comptes avec authentification Laravel Passport"
 * )
 * @OA\Server(
 *     url="https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1",
 *     description="Production Server (Render)"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="passport",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter your Bearer token obtained from /api/v1/login"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
