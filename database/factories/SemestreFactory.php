<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Semestre> */
class SemestreFactory extends Factory
{
    public function definition(): array
    {
        $n = fake()->unique()->numberBetween(1, 99);

        return ['code' => "S$n", 'libelle' => "Semestre $n", 'ordre' => $n];
    }
}
