<?php

namespace App\Models;

use App\Support\PersonName;
use Illuminate\Database\Eloquent\Model;

class StudentWhitelist extends Model
{
    protected $table = 'student_whitelist';

    protected $fillable = [
        'student_id',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'contact_number',
    ];

    public function getNameAttribute(): string
    {
        return PersonName::compose($this->first_name, $this->middle_name, $this->last_name);
    }

    public static function findByStudentId(string $studentId): ?self
    {
        return static::query()->where('student_id', $studentId)->first();
    }

    public static function notListedMessage(): string
    {
        return 'This EVSU email is not on the official student list. Only a listed student can request a leave. Ask the Registrar if your email should be added.';
    }

    public static function findByEmail(?string $email): ?self
    {
        $email = strtolower(trim((string) $email));
        if ($email === '') {
            return null;
        }

        return static::query()->whereRaw('LOWER(email) = ?', [$email])->first();
    }
}
