<?php

namespace App\Policies;

use App\Models\Filiere;
use App\Models\User;

/** RG23 : seul l'admin ajoute, modifie ou supprime une filière. */
class FilierePolicy
{
    public function create(User $user): bool
    {
        return $user->estAdmin();
    }

    public function update(User $user, Filiere $filiere): bool
    {
        return $user->estAdmin();
    }

    /** RG11 : pas de suppression d'une filière qui a déjà des promotions. */
    public function delete(User $user, Filiere $filiere): bool
    {
        return $user->estAdmin() && ! $filiere->promotions()->exists();
    }
}
