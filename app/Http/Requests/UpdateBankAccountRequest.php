<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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