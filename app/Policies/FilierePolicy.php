<?php

namespace App\Policies;

use App\Models\Filiere;
use App\Models\User;

/**
 * Droits sur une filière.
 *  - consulter : admin (toutes) ou chef (la sienne)      -> RG18, RG19
 *  - gérer     : admin uniquement                         -> RG23
 * Laravel associe automatiquement cette Policy au modèle Filiere (même nom).
 */
class FilierePolicy
{
    /** Voir les promotions et PV de la filière. */
    public function view(User $user, Filiere $filiere): bool
    {
        return $user->estAdmin() || $user->dirige($filiere);
    }

    public function create(User $user): bool
    {
        return $user->estAdmin();
    }

    public function update(User $user, Filiere $filiere): bool
    {
        return $user->estAdmin();
    }

    /** RG11 : on ne supprime pas une filière qui a déjà des promotions. */
    public function delete(User $user, Filiere $filiere): bool
    {
        return $user->estAdmin() && ! $filiere->promotions()->exists();
    }
}
