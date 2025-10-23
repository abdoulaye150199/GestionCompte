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
            'nci' => 'required|string|unique:users,nci|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'telephone' => 'required|string|unique:users,telephone|max:20',
            'adresse' => 'required|string|max:500',
            'role_id' => 'required|exists:roles,id',
            'login' => 'nullable|string|unique:users,login|max:255',
            'password' => 'nullable|string|min:8',
        ];
    }
}
