<?php

namespace Database\Factories;

use App\Models\Filiere;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Promotion> */
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
