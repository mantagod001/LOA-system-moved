<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StaffPasswordReset extends Model
{
    protected $fillable = [
        'email',
        'request_token_hash',
        'otp_hash',
        'otp_expires_at',
        'otp_verified_at',
        'reset_token_hash',
        'reset_token_expires_at',
        'used_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'otp_expires_at'          => 'datetime',
            'otp_verified_at'         => 'datetime',
            'reset_token_expires_at'  => 'datetime',
            'used_at'                 => 'datetime',
        ];
    }

    public static function hashPlain(string $plain): string
    {
        return hash('sha256', $plain);
    }

    /**
     * Start a reset for an email. Always creates a request so the UI
     * stays the same whether or not the account exists.
     *
     * @return array{0: self, 1: string, 2: string} record, request token, otp
     */
    public static function issue(string $email, ?string $ipAddress = null): array
    {
        self::query()
            ->where('email', $email)
            ->whereNull('used_at')
            ->delete();

        $plainToken = Str::random(40);
        $plainOtp = (string) random_int(100000, 999999);

        $record = self::create([
            'email'              => $email,
            'request_token_hash' => self::hashPlain($plainToken),
            'otp_hash'           => self::hashPlain($plainOtp),
            'otp_expires_at'     => now()->addMinutes(10),
            'ip_address'         => $ipAddress,
        ]);

        return [$record, $plainToken, $plainOtp];
    }

    public static function findOpenRequest(string $plainToken): ?self
    {
        if (! preg_match('/^[A-Za-z0-9]{40}$/', $plainToken)) {
            return null;
        }

        return self::query()
            ->where('request_token_hash', self::hashPlain($plainToken))
            ->whereNull('used_at')
            ->first();
    }

    public static function findOpenReset(string $plainToken): ?self
    {
        if (! preg_match('/^[A-Za-z0-9]{40}$/', $plainToken)) {
            return null;
        }

        return self::query()
            ->where('reset_token_hash', self::hashPlain($plainToken))
            ->whereNull('used_at')
            ->where('reset_token_expires_at', '>', now())
            ->whereNotNull('otp_verified_at')
            ->first();
    }

    public function verifyOtp(string $plainOtp): bool
    {
        if ($this->otp_verified_at) {
            return true;
        }

        if (! $this->otp_expires_at || $this->otp_expires_at->isPast()) {
            return false;
        }

        if (! hash_equals($this->otp_hash ?? '', self::hashPlain($plainOtp))) {
            return false;
        }

        $this->update(['otp_verified_at' => now()]);

        return true;
    }

    public function issueResetToken(): string
    {
        $plain = Str::random(40);

        $this->update([
            'reset_token_hash'        => self::hashPlain($plain),
            'reset_token_expires_at'  => now()->addHour(),
        ]);

        return $plain;
    }
}
