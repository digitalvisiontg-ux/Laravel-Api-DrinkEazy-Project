<?php

namespace App\Providers;

class PhoneNormalizer
{
    /**
     * Normalise un numéro Togo en E.164 (+228XXXXXXXX)
     * Accepte les numéros locaux 8 chiffres et vérifie préfixes mobiles valides.
     */
    public static function normalizeTg(string $input): ?string
    {
        // Retire tous les caractères non numériques
        $raw = trim(preg_replace('/[^\d]/', '', $input));

        // Liste des préfixes mobiles valides Togo
        $validPrefixes = ['90', '91', '92', '93', '70', '71', '72', '73', '78', '79', '96', '97', '98', '99'];

        // Cas +228XXXXXXXX
        if (preg_match('/^\+228\d{8}$/', $input)) {
            $localPrefix = substr($input, 4, 2);
            if (in_array($localPrefix, $validPrefixes)) {
                return $input;
            }
            return null;
        }

        // Cas 8 chiffres locaux
        if (preg_match('/^\d{8}$/', $raw)) {
            $localPrefix = substr($raw, 0, 2);
            if (in_array($localPrefix, $validPrefixes)) {
                return '+228' . $raw;
            }
            return null;
        }

        // Numéro invalide
        return null;
    }

    /**
     * Normalise un numéro Gabon en E.164 (+241XXXXXXXX)
     * Accepte les numéros locaux 8 chiffres et vérifie préfixes mobiles valides.
     */
    public static function normalizeGab(string $input): ?string
    {
        // Retire tous les caractères non numériques
        $raw = trim(preg_replace('/[^\d]/', '', $input));

        // Liste des préfixes mobiles valides Gabon
        $validPrefixes = ['06','07','08','09','01','02','03','04','05']; // exemple selon ARCEP/Gabon (adapter si nécessaire)

        // Cas +241XXXXXXXX
        if (preg_match('/^\+241\d{8}$/', $input)) {
            $localPrefix = substr($input, 3, 2);
            if (in_array($localPrefix, $validPrefixes)) {
                return $input;
            }
            return null;
        }

        // Cas 8 chiffres locaux
        if (preg_match('/^\d{8}$/', $raw)) {
            $localPrefix = substr($raw, 0, 2);
            if (in_array($localPrefix, $validPrefixes)) {
                return '+241' . $raw;
            }
            return null;
        }

        // Cas avec 0XXXXXXX (0 + 8 chiffres)
        if (preg_match('/^0\d{8}$/', $raw)) {
            $localPrefix = substr($raw, 0, 2);
            if (in_array($localPrefix, $validPrefixes)) {
                return '+241' . substr($raw, 1); // enlève le 0 initial
            }
            return null;
        }

        // Numéro invalide
        return null;
    }
}
