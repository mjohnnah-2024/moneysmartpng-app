<?php

namespace App\Support;

class Sanitizer
{
    /**
     * Strip HTML tags, trim whitespace, and limit length.
     */
    public static function text(?string $value, int $maxLength = 255): ?string
    {
        if ($value === null) {
            return null;
        }

        return mb_substr(trim(strip_tags($value)), 0, $maxLength);
    }

    /**
     * Ensure a valid numeric amount within reasonable bounds.
     */
    public static function amount(mixed $value, float $max = 999999.99): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $number = filter_var($value, FILTER_VALIDATE_FLOAT);

        if ($number === false || $number < 0) {
            return null;
        }

        return min(round($number, 2), $max);
    }
}
