<?php

namespace App\Support;

class StudentId
{
    public static function normalize(?string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $raw) ?? '';

        if (strlen($digits) < 8 || strlen($digits) > 10) {
            return null;
        }

        return substr($digits, 0, 4).'-'.substr($digits, 4);
    }
}
