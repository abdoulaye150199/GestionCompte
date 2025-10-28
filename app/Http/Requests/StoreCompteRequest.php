<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompteRequest extends FormRequest
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
            'user_id' => 'required|uuid',
            'type' => 'required|in:epargne,cheque',
            'solde' => 'numeric|min:0',
            'devise' => 'string|max:10',
            'statut' => 'in:actif,bloque,ferme',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'L\'ID de l\'utilisateur est obligatoire.',
            'user_id.uuid' => 'L\'ID de l\'utilisateur doit être un UUID valide.',
            'user_id.exists' => 'L\'utilisateur spécifié n\'existe pas.',
            'type.required' => 'Le type de compte est obligatoire.',
            'type.in' => 'Le type de compte doit être soit epargne soit cheque.',
            'solde.numeric' => 'Le solde doit être un nombre.',
            'solde.min' => 'Le solde ne peut pas être négatif.',
            'devise.string' => 'La devise doit être une chaîne de caractères.',
            'devise.max' => 'La devise ne peut pas dépasser 10 caractères.',
            'statut.in' => 'Le statut doit être actif, bloque ou ferme.',
        ];
    }
}
