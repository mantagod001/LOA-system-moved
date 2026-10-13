<?php

namespace App\Enums;

enum UserRole: string
{
    case Administrator = 'administrator';
    case DepartmentHead = 'department_head';
    case SasoOfficer = 'saso_officer';
    case CampusDirector = 'campus_director';
    case President = 'president';
    case Registrar = 'registrar';
    case Guidance = 'guidance';

    public function label(): string
    {
        return match ($this) {
            self::Administrator => 'System Administrator',
            self::DepartmentHead => 'Department Head',
            self::SasoOfficer => 'SASO Officer',
            self::CampusDirector => 'Campus Director',
            self::President => 'President',
            self::Registrar => 'Registrar',
            self::Guidance => 'Guidance Office',
        };
    }

    public function needsDepartment(): bool
    {
        return $this === self::DepartmentHead;
    }

    public function is(self $other): bool
    {
        return $this === $other;
    }
}
