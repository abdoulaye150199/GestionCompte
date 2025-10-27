<?php

namespace App\OpenApi;

/**
 * Global OpenAPI configuration and shared components
 *
 * @OA\Info(
 *     title="API de Gestion des Clients & Comptes",
 *     version="1.1.0",
 *     description="API RESTful pour la gestion des clients et de leurs comptes bancaires"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api/v1",
 *     description="Serveur de développement"
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
