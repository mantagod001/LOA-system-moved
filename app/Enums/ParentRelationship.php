<?php

namespace App\Enums;

enum ParentRelationship: string
{
    case Mother = 'mother';
    case Father = 'father';
    case Sister = 'sister';
    case Brother = 'brother';
    case Guardian = 'guardian';
    case Spouse = 'spouse';
    case Grandmother = 'grandmother';
    case Grandfather = 'grandfather';
    case Aunt = 'aunt';
    case Uncle = 'uncle';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Mother => 'Mother',
            self::Father => 'Father',
            self::Sister => 'Sister',
            self::Brother => 'Brother',
            self::Guardian => 'Guardian',
            self::Spouse => 'Spouse',
            self::Grandmother => 'Grandmother',
            self::Grandfather => 'Grandfather',
            self::Aunt => 'Aunt',
            self::Uncle => 'Uncle',
            self::Other => 'Other',
        };
    }
}
