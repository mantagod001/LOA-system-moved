<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Support\PersonName;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
        'role',
        'department_id',
        'invitation_token',
        'invitation_sent_at',
        'invitation_accepted_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'invitation_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'        => 'datetime',
            'password'                 => 'hashed',
            'role'                     => UserRole::class,
            'invitation_sent_at'       => 'datetime',
            'invitation_accepted_at'   => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $user) {
            if (filled($user->first_name) || filled($user->last_name)) {
                $user->name = PersonName::compose($user->first_name, $user->middle_name, $user->last_name);
            }
        });
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // ── Invitation helpers ────────────────────────────────────────────────────

    /** Whether the account setup has been completed by the invited user. */
    public function isActivated(): bool
    {
        return $this->invitation_accepted_at !== null;
    }

    /** Whether an invitation was sent but not yet accepted. */
    public function isPendingInvitation(): bool
    {
        return $this->invitation_sent_at !== null && $this->invitation_accepted_at === null;
    }

    /**
     * Generate a plain invitation token, store its SHA-256 hash, return the plain token.
     * The plain token is placed in the invite URL — the hash is what we store.
     */
    public function generateInvitationToken(): string
    {
        $plain = Str::random(48);

        $this->forceFill([
            'invitation_token'    => hash('sha256', $plain),
            'invitation_sent_at'  => now(),
            'invitation_accepted_at' => null,
        ])->save();

        return $plain;
    }

    /** Find a user by a plain invitation token (hashes it then queries). */
    public static function findByInvitationToken(string $plain): ?self
    {
        return static::query()
            ->where('invitation_token', hash('sha256', $plain))
            ->first();
    }

    // ── Authorization helpers ─────────────────────────────────────────────────

    public function canViewLoaRequest(LoaRequest $request): bool
    {
        if ($this->role->is(UserRole::DepartmentHead)) {
            return $this->department_id !== null
                && (int) $request->department_id === (int) $this->department_id;
        }

        if ($this->role->is(UserRole::President)) {
            return $request->isExtension();
        }

        return true;
    }
}
