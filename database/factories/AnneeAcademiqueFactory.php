<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\AnneeAcademique> */
class AnneeAcademiqueFactory extends Factory
{
    public function definition(): array
    {
        $debut = fake()->unique()->numberBetween(2010, 2030);

        return ['libelle' => $debut.'-'.($debut + 1)];
    }
}
