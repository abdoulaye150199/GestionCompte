<?php

namespace App\Http\Requests;

use App\Traits\Validators\ValidationTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BlocageCompteRequest extends FormRequest
{
    use ValidationTrait;
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // handled in passedValidation via ValidationTrait
        return [];
    }

    public function messages()
    {
        return [
            'date_debut_blocage.required' => 'La date de début du blocage est requise.',
            'date_fin_blocage.required' => 'La date de fin du blocage est requise.',
            'date_fin_blocage.after' => 'La date de fin doit être postérieure à la date de début.',
            'motif_blocage.required' => 'Le motif de blocage est requis.',
        ];
    }

    protected function passedValidation()
    {
        $errors = $this->validateBlocageComptePayload($this->all());
        if (!empty($errors)) {
            throw new HttpResponseException(response()->json(['success' => false, 'errors' => $errors], 400));
        }
    }
}
