<?php

namespace Database\Factories;

use App\Models\Filiere;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Promotion> */
class PromotionFactory extends Factory
{
    public function definition(): array
    {
        $debut = fake()->unique()->numberBetween(2010, 2030);

        return [
            'filiere_id' => Filiere::factory(),
            'libelle' => 'Promotion '.$debut.'-'.($debut + 3),
        ];
    }
}
