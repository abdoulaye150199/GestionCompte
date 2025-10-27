<?php

namespace App\OpenApi;

/**
 * Global OpenAPI configuration and shared components
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 *
 * @OA\Info(
 *     title="API de Gestion des Clients & Comptes",
 *     version="1.1.0",
 *     description="API RESTful pour la gestion des clients et de leurs comptes bancaires"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class OpenApiConfig {}
