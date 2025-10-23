<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreBankAccountRequest",
 *     type="object",
 *     title="Store Bank Account Request",
 *     required={"type", "client_id"},
 *     @OA\Property(property="type", type="string", enum={"savings", "checking"}, example="savings"),
 *     @OA\Property(property="client_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="balance", type="number", format="float", example=0.00),
 *     @OA\Property(property="account_number", type="string", example="FR7630001007941234567890185")
 * )
 */
class StoreBankAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:savings,checking'],
            'client_id' => ['required', 'uuid', 'exists:clients,id'],
            'balance' => ['sometimes', 'numeric', 'min:0'],
            'account_number' => ['sometimes', 'string', 'unique:bank_accounts,account_number'],
        ];
    }
}
