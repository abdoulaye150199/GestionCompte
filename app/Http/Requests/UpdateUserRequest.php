<?php

namespace App\Http\Requests;

use App\Rules\ValidNci;
use App\Rules\ValidTelephone;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'nom' => 'sometimes|string|max:255',
            'nci' => ['sometimes', 'string', 'max:255', new ValidNci()],
            'email' => 'sometimes|email|max:255',
            'telephone' => ['sometimes', 'string', 'max:20', new ValidTelephone()],
            'adresse' => 'sometimes|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'nci.string' => 'Le numéro CNI doit être une chaîne de caractères.',
            'nci.max' => 'Le numéro CNI ne peut pas dépasser 255 caractères.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.max' => 'L\'adresse email ne peut pas dépasser 255 caractères.',
            'telephone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'telephone.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères.',
            'adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
            'adresse.max' => 'L\'adresse ne peut pas dépasser 500 caractères.',
        ];
    }
}
