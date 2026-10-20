<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class LoaAccessToken extends Model
{
    protected $fillable = [
        'student_id',
        'full_name',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'token_hash',
        'expires_at',
        'used_at',
        'ip_address',
        'otp_hash',
        'otp_expires_at',
        'otp_verified',
        'purpose',
        'parent_loa_id',
    ];

    protected function casts(): array
    {
        return [
            'expires_at'    => 'datetime',
            'used_at'       => 'datetime',
            'otp_expires_at'=> 'datetime',
            'otp_verified'  => 'boolean',
        ];
    }

    public function loaRequest(): HasOne
    {
        return $this->hasOne(LoaRequest::class);
    }

    public function parentLoa(): BelongsTo
    {
        return $this->belongsTo(LoaRequest::class, 'parent_loa_id');
    }

    public function isExtension(): bool
    {
        return $this->purpose === 'extension';
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public static function hashPlainToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    /**
     * Issue a pending token after the student enters identity + email.
     * Returns [record, plainToken, plainOtp].
     *
     * @param  array{student_id: string, first_name: string, middle_name?: ?string, last_name: string}  $identity
     * @param  array{purpose?: string, parent_loa_id?: int|null}  $options
     */
    public static function issueForEmail(string $email, array $identity = [], ?string $ipAddress = null, array $options = []): array
    {
        $plainToken = Str::random(40);
        $plainOtp   = (string) random_int(100000, 999999); // 6-digit OTP
        $fullName = \App\Support\PersonName::compose(
            $identity['first_name'] ?? '',
            $identity['middle_name'] ?? null,
            $identity['last_name'] ?? '',
        );

        $record = self::create([
            'student_id'    => $identity['student_id'] ?? '',
            'full_name'     => $fullName,
            'first_name'    => $identity['first_name'] ?? '',
            'middle_name'   => $identity['middle_name'] ?? null,
            'last_name'     => $identity['last_name'] ?? '',
            'email'         => $email,
            'token_hash'    => self::hashPlainToken($plainToken),
            'expires_at'    => now()->addHours(24),
            'ip_address'    => $ipAddress,
            'otp_hash'      => hash('sha256', $plainOtp),
            'otp_expires_at'=> now()->addMinutes(10),
            'otp_verified'  => false,
            'purpose'       => $options['purpose'] ?? 'leave',
            'parent_loa_id' => $options['parent_loa_id'] ?? null,
        ]);

        return [$record, $plainToken, $plainOtp];
    }

    /**
     * Verify the OTP for a pending token.
     * Returns true and marks verified, or false if invalid/expired.
     */
    public function verifyOtp(string $plainOtp): bool
    {
        if ($this->otp_verified) {
            return true; // already verified
        }

        if (! $this->otp_expires_at || $this->otp_expires_at->isPast()) {
            return false; // expired
        }

        if (! hash_equals($this->otp_hash ?? '', hash('sha256', $plainOtp))) {
            return false; // wrong code
        }

        $this->update(['otp_verified' => true]);

        return true;
    }
}
