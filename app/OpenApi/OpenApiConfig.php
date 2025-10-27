<?php

namespace App\OpenApi;

/**
 * @OA\Info(
 *     title="API de Gestion des Clients & Comptes",
 *     version="1.2.0",
 *     description="API RESTful pour la gestion des clients et de leurs comptes bancaires avec archivage automatique"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api/v1",
 *     description="Serveur de développement"
 * )
 * @OA\Server(
 *     url="https://gestioncompte-api.onrender.com/abdoulaye.diallo/api/v1",
 *     description="Serveur de production"
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