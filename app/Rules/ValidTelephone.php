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
        // Format téléphone Sénégalais: +221 suivi de 9 chiffres (77, 78, 76, 70, 33, etc.)
        if (!preg_match('/^\+221[0-9]{9}$/', $value)) {
            $fail('Le numéro de téléphone doit être au format +221XXXXXXXXX.');
        }
    }
}
