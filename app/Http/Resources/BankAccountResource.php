<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="BankAccountResource",
 *     type="object",
 *     title="Bank Account Resource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="account_number", type="string", example="FR7630001007941234567890185"),
 *     @OA\Property(property="balance", type="number", format="float", example=1000.50),
 *     @OA\Property(property="client_id", type="integer", example=1),
 *     @OA\Property(
 *         property="client",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="John Doe"),
 *         @OA\Property(property="email", type="string", format="email", example="john.doe@example.com")
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class BankAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_number' => $this->account_number,
            'balance' => $this->balance,
            'client_id' => $this->client_id,
            'client' => $this->whenLoaded('client'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}