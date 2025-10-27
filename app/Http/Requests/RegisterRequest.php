<?php

namespace App\Http\Requests;

use App\Rules\ValidNci;
use App\Rules\ValidTelephone;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'login' => 'required|string|unique:users,login|max:255',
            'password' => 'required|string|min:8',
            'nom' => 'required|string|max:255',
            'nci' => ['required', 'string', 'max:255', 'unique:clients,nci', new ValidNci()],
            'email' => 'required|email|max:255|unique:clients,email',
            'telephone' => ['required', 'string', 'max:20', 'unique:clients,telephone', new ValidTelephone()],
            'adresse' => 'required|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'login.required' => 'Le login est obligatoire.',
            'login.string' => 'Le login doit être une chaîne de caractères.',
            'login.unique' => 'Ce login est déjà utilisé.',
            'login.max' => 'Le login ne peut pas dépasser 255 caractères.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'nom.required' => 'Le nom est obligatoire.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'nci.required' => 'Le numéro CNI est obligatoire.',
            'nci.string' => 'Le numéro CNI doit être une chaîne de caractères.',
            'nci.max' => 'Le numéro CNI ne peut pas dépasser 255 caractères.',
            'nci.unique' => 'Ce numéro CNI est déjà utilisé.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.max' => 'L\'adresse email ne peut pas dépasser 255 caractères.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'telephone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'telephone.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères.',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'adresse.required' => 'L\'adresse est obligatoire.',
            'adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
            'adresse.max' => 'L\'adresse ne peut pas dépasser 500 caractères.',
        ];
    }
}
