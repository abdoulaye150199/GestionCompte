<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserIndexRequest extends FormRequest
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
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
            'role' => 'nullable|string|in:admin,client',
            'search' => 'nullable|string|max:255',
            'sort' => 'nullable|string|in:dateCreation,nom',
            'order' => 'nullable|string|in:asc,desc',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'page.integer' => 'Le numéro de page doit être un entier.',
            'page.min' => 'Le numéro de page doit être au moins 1.',
            'limit.integer' => 'La limite doit être un entier.',
            'limit.min' => 'La limite doit être au moins 1.',
            'limit.max' => 'La limite ne peut pas dépasser 100.',
            'role.string' => 'Le rôle doit être une chaîne de caractères.',
            'role.in' => 'Le rôle doit être soit admin soit client.',
            'search.string' => 'La recherche doit être une chaîne de caractères.',
            'search.max' => 'La recherche ne peut pas dépasser 255 caractères.',
            'sort.string' => 'Le champ de tri doit être une chaîne de caractères.',
            'sort.in' => 'Le champ de tri doit être soit dateCreation soit nom.',
            'order.string' => 'L\'ordre de tri doit être une chaîne de caractères.',
            'order.in' => 'L\'ordre de tri doit être soit asc soit desc.',
        ];
    }
}
