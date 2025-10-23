<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateBankAccountRequest",
 *     type="object",
 *     title="Update Bank Account Request",
 *     @OA\Property(property="account_type", type="string", enum={"savings", "checking"}, example="savings"),
 *     @OA\Property(property="balance", type="number", format="float", example=1000.50),
 *     @OA\Property(property="client_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
 * )
 */
class UpdateBankAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_type' => ['sometimes', 'required', 'string', 'in:savings,checking'],
            'balance' => ['sometimes', 'required', 'numeric', 'min:0'],
            'client_id' => ['sometimes', 'required', 'uuid', 'exists:clients,id'],
        ];
    }
}