<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name'             => fake()->firstName(),
            'middle_name'            => fake()->optional(0.4)->firstName(),
            'last_name'              => fake()->lastName(),
            'email'                  => fake()->unique()->safeEmail(),
            'email_verified_at'      => now(),
            'password'               => static::$password ??= Hash::make('password'),
            'role'                   => UserRole::DepartmentHead,
            'remember_token'         => Str::random(10),
            // Factory-created users are pre-activated (no pending invite).
            // Tests that need an unactivated user should explicitly set this to null.
            'invitation_accepted_at' => now(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
