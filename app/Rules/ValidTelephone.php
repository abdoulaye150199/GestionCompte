<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidTelephone implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Validate Senegalese phone number format (+221 followed by 9 digits) without regex
        if (strlen($value) !== 13) {
            $fail('Le numéro de téléphone doit contenir exactement 13 caractères.');
            return;
        }

        if (substr($value, 0, 4) !== '+221') {
            $fail('Le numéro de téléphone doit commencer par +221.');
            return;
        }

        $phoneNumber = substr($value, 4);
        if (!is_numeric($phoneNumber)) {
            $fail('Le numéro de téléphone doit contenir uniquement des chiffres après +221.');
            return;
        }

        if (strlen($phoneNumber) !== 9) {
            $fail('Le numéro de téléphone doit contenir 9 chiffres après +221.');
            return;
        }

        // Validate that it starts with valid Senegalese mobile prefixes
        $validPrefixes = ['70', '76', '77', '78'];
        $prefix = substr($phoneNumber, 0, 2);
        if (!in_array($prefix, $validPrefixes)) {
            $fail('Le numéro de téléphone doit commencer par un préfixe valide (70, 76, 77, ou 78).');
        }
    }
}
