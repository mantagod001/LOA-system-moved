<?php

namespace App\Enums;

enum AcademicSemester: string
{
    case First = 'first';
    case Second = 'second';
    case Midyear = 'midyear';

    public function label(): string
    {
        return match ($this) {
            self::First => '1st Semester',
            self::Second => '2nd Semester',
            self::Midyear => 'Midyear',
        };
    }

    /** @return list<string> */
    public static function yearOptions(?string $include = null): array
    {
        $now = now();
        $start = $now->month >= 6 ? $now->year : $now->year - 1;
        $years = [];

        foreach (range($start - 1, $start + 1) as $year) {
            $years[] = $year.'-'.($year + 1);
        }

        if (self::isValidYear((string) $include) && ! in_array($include, $years, true)) {
            $years[] = $include;
            sort($years);
        }

        return $years;
    }

    public static function isValidYear(?string $year): bool
    {
        if (! is_string($year) || ! preg_match('/^(\d{4})-(\d{4})$/', $year, $matches)) {
            return false;
        }

        return ((int) $matches[2]) === ((int) $matches[1] + 1);
    }
}
