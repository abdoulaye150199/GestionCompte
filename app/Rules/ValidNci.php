<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidNci implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Validate Senegalese NCI format (12 digits) without regex
        if (!is_numeric($value)) {
            $fail('Le numéro CNI doit contenir uniquement des chiffres.');
            return;
        }

        if (strlen($value) !== 12) {
            $fail('Le numéro CNI doit contenir exactement 12 chiffres.');
            return;
        }

        // Additional validation: check if it's a valid Senegalese CNI format
        // CNI typically starts with department code (2 digits) followed by other numbers
        $departmentCode = substr($value, 0, 2);
        if (!is_numeric($departmentCode) || $departmentCode < 1 || $departmentCode > 99) {
            $fail('Le numéro CNI doit commencer par un code département valide (01-99).');
        }
    }
}
