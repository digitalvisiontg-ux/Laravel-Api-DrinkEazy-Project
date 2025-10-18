<?php

namespace App\Rules;

use App\Providers\PhoneNormalizer;
use Illuminate\Contracts\Validation\Rule;


class ValidTgPhone implements Rule
{
    public function passes($attribute, $value)
    {
        // Appelle le normalizer Togo
        $normalized = PhoneNormalizer::normalizeTg($value);
        return !is_null($normalized);
    }

    public function message()
    {
        return 'Le numéro entré n\'est pas un numéro mobile valide du Togo (+228, préfixes 90-99).';
    }
}
