<?php

namespace Database\Factories;

use App\Models\Semestre;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Semestre> */
class SemestreFactory extends Factory
{
    public function definition(): array
    {
        $n = fake()->unique()->numberBetween(1, 99);

        return ['code' => "S$n", 'libelle' => "Semestre $n", 'ordre' => $n];
    }
}
