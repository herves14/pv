<?php

namespace Database\Factories;

use App\Enums\SessionPv;
use App\Models\AnneeAcademique;
use App\Models\ProcesVerbal;
use App\Models\Promotion;
use App\Models\Semestre;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ProcesVerbal> */
class ProcesVerbalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'promotion_id' => Promotion::factory(),
            'annee_academique_id' => AnneeAcademique::factory(),
            'semestre_id' => Semestre::factory(),
            'session' => SessionPv::Normale,
            'fichier_chemin' => 'pv/'.Str::uuid().'.pdf',
            'fichier_nom_original' => 'pv-scan.pdf',
            'fichier_taille' => 1024,
            'televerse_par' => User::factory(),
        ];
    }
}
