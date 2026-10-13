<?php

namespace App\Support;

class PersonName
{
    public static function compose(?string $first, ?string $middle, ?string $last): string
    {
        return collect([$first, $middle, $last])
            ->map(fn ($part) => trim((string) $part))
            ->filter(fn ($part) => $part !== '')
            ->implode(' ');
    }
}
