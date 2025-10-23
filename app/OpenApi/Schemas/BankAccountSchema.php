<?php

namespace App\OpenApi\Schemas;

/**
 * @OA\Schema(
 *     schema="BankAccount",
 *     type="object",
 *     title="Bank Account",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="account_number", type="string", example="FR7630001007941234567890185"),
 *     @OA\Property(property="balance", type="number", format="float", example=1000.50),
 *     @OA\Property(property="client_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class BankAccountSchema {}