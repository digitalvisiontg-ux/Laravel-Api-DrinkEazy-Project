<?php

namespace App\Rules;

use App\Providers\PhoneNormalizer;
use Illuminate\Contracts\Validation\Rule;


class ValidGbPhone implements Rule
{
    public function passes($attribute, $value)
    {
        // Appelle le normalizer Gabon
        $normalized = PhoneNormalizer::normalizeGab($value);
        return !is_null($normalized);
    }

    public function message()
    {
        return 'Le numéro entré n\'est pas un numéro mobile valide du Gabon (+241, préfixes valides).';
    }
}
