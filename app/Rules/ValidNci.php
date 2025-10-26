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
        // Format NCI Sénégalais: 13 chiffres ou 14 avec lettre
        if (!preg_match('/^[0-9]{13}[A-Z]?$/i', $value)) {
            $fail('Le numéro NCI doit contenir 13 chiffres suivis optionnellement d\'une lettre.');
        }
    }
}
