<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'nom' => 'required|string|max:255',
            'nci' => 'required|string|max:255|unique:clients,nci',
            'email' => 'required|email|max:255|unique:clients,email',
            'telephone' => 'required|string|max:20|unique:clients,telephone',
            'adresse' => 'required|string|max:500',
            'role_id' => 'required|uuid|exists:roles,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
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
            'role_id.required' => 'L\'ID du rôle est obligatoire.',
            'role_id.uuid' => 'L\'ID du rôle doit être un UUID valide.',
            'role_id.exists' => 'Le rôle spécifié n\'existe pas.',
        ];
    }
}
