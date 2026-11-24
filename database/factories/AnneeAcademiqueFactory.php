<?php

namespace Database\Factories;

use App\Models\AnneeAcademique;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AnneeAcademique> */
class AnneeAcademiqueFactory extends Factory
{
    public function definition(): array
    {
        $debut = fake()->unique()->numberBetween(2010, 2030);

        return ['libelle' => $debut.'-'.($debut + 1)];
    }
}
