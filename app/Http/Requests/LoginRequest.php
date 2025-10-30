<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            // require either 'login' (username/email) or 'identifier'
            'login' => 'nullable|string|max:255|required_without:identifier',
            'identifier' => 'nullable|string|max:255|required_without:login',
            'password' => 'required|string|min:6',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'login.required_without' => 'Le login ou l\'identifiant est requis.',
            'identifier.required_without' => 'Le login ou l\'identifiant est requis.',
            'login.string' => 'Le login doit être une chaîne de caractères.',
            'login.max' => 'Le login ne peut pas dépasser 255 caractères.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
        ];
    }
}
