<?php

namespace App\Support;

/**
 * Formats centavo amounts for people (emails, SMS, CSV).
 */
final class Money
{
    public static function format(int $centavos): string
    {
        $decimals = $centavos % 100 === 0 ? 0 : 2;

        return '₱'.number_format($centavos / 100, $decimals);
    }
}
