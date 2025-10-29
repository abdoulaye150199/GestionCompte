<?php

namespace App\OpenApi;

/**
 * Global OpenAPI configuration and shared components
 *
 * @OA\Server(
 *     url="https://gestioncompte-api.onrender.com",
 *     description="Primary server (Render)"
 * )

 * @OA\Server(
 *     url="https://production.abdoulaye.diallo",
 *     description="Secondary server (production.abdoulaye.diallo)"
 * )
 *
 * @OA\Info(
 *     title="API de Gestion des Clients & Comptes",
 *     version="1.0.0",
 *     description="API RESTful pour la gestion des clients et de leurs comptes bancaires avec authentification Laravel Passport"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Entrez votre jeton Bearer obtenu via /api/v1/login"
 * )
 */
class OpenApiConfig {}
