<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Filiere> */
class FiliereFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'libelle' => fake()->words(3, true),
        ];
    }
}
