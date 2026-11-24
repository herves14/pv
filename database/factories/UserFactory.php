<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Filiere;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => Role::Expedition,
            'filiere_id' => null,
            'actif' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(['role' => Role::Admin]);
    }

    public function chef(?Filiere $filiere = null): static
    {
        return $this->state(fn () => [
            'role' => Role::ChefDepartement,
            'filiere_id' => $filiere?->id ?? Filiere::factory(),
        ]);
    }

    public function inactif(): static
    {
        return $this->state(['actif' => false]);
    }
}
