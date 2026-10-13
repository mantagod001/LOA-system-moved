<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffUsersSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('12345678');

        $dcs = Department::query()->where('code', 'DCS')->first();
        $dte = Department::query()->where('code', 'DTE')->first();
        $htm = Department::query()->where('code', 'HTM')->orWhere('code', 'DBM')->first();
        $dit = Department::query()->where('code', 'DIT')->first();

        $users = [
            [
                'first_name' => 'System',
                'middle_name' => null,
                'last_name' => 'Administrator',
                'email' => 'admin@evsu.edu.ph',
                'role' => UserRole::Administrator,
                'department_id' => null,
            ],
            [
                'first_name' => 'Joseph Jaymel',
                'middle_name' => 'S.',
                'last_name' => 'Morpos',
                'email' => 'dcs.head@evsu.edu.ph',
                'role' => UserRole::DepartmentHead,
                'department_id' => $dcs?->id,
            ],
            [
                'first_name' => 'Guillermo',
                'middle_name' => 'M.',
                'last_name' => 'Sodomia',
                'email' => 'dte.head@evsu.edu.ph',
                'role' => UserRole::DepartmentHead,
                'department_id' => $dte?->id,
            ],
            [
                'first_name' => 'Lyra',
                'middle_name' => null,
                'last_name' => 'Calvez',
                'email' => 'htm.head@evsu.edu.ph',
                'role' => UserRole::DepartmentHead,
                'department_id' => $htm?->id,
            ],
            [
                'first_name' => 'Alan Reynaldo',
                'middle_name' => 'E.',
                'last_name' => 'Mabitad',
                'email' => 'dit.head@evsu.edu.ph',
                'role' => UserRole::DepartmentHead,
                'department_id' => $dit?->id,
            ],
            [
                'first_name' => 'Joergen',
                'middle_name' => 'T.',
                'last_name' => 'Arradaza',
                'email' => 'saso@evsu.edu.ph',
                'role' => UserRole::SasoOfficer,
                'department_id' => null,
            ],
            [
                'first_name' => 'Maricel',
                'middle_name' => 'A.',
                'last_name' => 'Gomez',
                'email' => 'director@evsu.edu.ph',
                'role' => UserRole::CampusDirector,
                'department_id' => null,
            ],
            [
                'first_name' => 'University',
                'middle_name' => null,
                'last_name' => 'President',
                'email' => 'president@evsu.edu.ph',
                'role' => UserRole::President,
                'department_id' => null,
            ],
            [
                'first_name' => 'Jonnah',
                'middle_name' => 'R.',
                'last_name' => 'Benitez',
                'email' => 'registrar@evsu.edu.ph',
                'role' => UserRole::Registrar,
                'department_id' => null,
            ],
            [
                'first_name' => 'Charlene',
                'middle_name' => null,
                'last_name' => 'Pita',
                'email' => 'guidance@evsu.edu.ph',
                'role' => UserRole::Guidance,
                'department_id' => null,
            ],
        ];

        foreach ($users as $record) {
            $user = User::query()->where('email', $record['email'])->first();

            if ($user) {
                $user->update([
                    'first_name'    => $record['first_name'],
                    'middle_name'   => $record['middle_name'],
                    'last_name'     => $record['last_name'],
                    'role'          => $record['role'],
                    'department_id' => $record['department_id'],
                ]);
                continue;
            }

            User::query()->create([
                'first_name'              => $record['first_name'],
                'middle_name'             => $record['middle_name'],
                'last_name'               => $record['last_name'],
                'email'                   => $record['email'],
                'password'                => $password,
                'role'                    => $record['role'],
                'department_id'           => $record['department_id'],
                // Seeded accounts are pre-activated — no invitation flow needed.
                'invitation_accepted_at'  => now(),
                'email_verified_at'       => now(),
            ]);
        }
    }
}
