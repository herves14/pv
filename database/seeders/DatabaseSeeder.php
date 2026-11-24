<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Filiere;
use App\Models\Semestre;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 8 filières de départ (= départements) ; l'admin peut en ajouter d'autres (RG23). Liste PROVISOIRE : codes et libellés
        // à vérifier auprès de la scolarité de l'ENEAM avant la démonstration.
        $filieres = [
            'GRH' => 'Gestion des Ressources Humaines',
            'GFC' => 'Gestion Financière et Comptable',
            'BF' => 'Banque et Finance',
            'MAC' => 'Marketing et Action Commerciale',
            'GTL' => 'Gestion des Transports et Logistique',
            'AD' => 'Assistanat de Direction',
            'STAT' => 'Statistique',
            'PLAN' => 'Planification',
        ];
        foreach ($filieres as $code => $libelle) {
            Filiere::updateOrCreate(['code' => $code], ['libelle' => $libelle]);
        }

        for ($i = 1; $i <= 6; $i++) {
            Semestre::updateOrCreate(['code' => "S$i"], ['libelle' => "Semestre $i", 'ordre' => $i]);
        }

        // Premier compte administrateur (le directeur). Identifiants dans .env.
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@eneam.local')],
            [
                'name' => env('ADMIN_NAME', 'Direction ENEAM'),
                'password' => env('ADMIN_PASSWORD', 'changez-moi'),
                'role' => Role::Admin,
                'actif' => true,
            ]
        );
    }
}
