<?php

namespace App\Policies;

use App\Models\ProcesVerbal;
use App\Models\User;

/**
 * Droits sur un PV précis (section 6 de la modélisation).
 * Laravel découvre cette Policy automatiquement grâce à son nom.
 */
class ProcesVerbalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->actif;
    }

    /** RG18 (admin), RG19 (chef : sa filière), RG20 (expédition : ses envois). */
    public function view(User $user, ProcesVerbal $pv): bool
    {
        if ($user->estAdmin()) {
            return true;
        }

        if ($user->estChef()) {
            return $user->filiere_id !== null
                && $pv->promotion->filiere_id === $user->filiere_id;
        }

        return $pv->televerse_par === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->estAdmin() || $user->estExpedition();
    }

    /** RG17 : remplacer le fichier. */
    public function update(User $user, ProcesVerbal $pv): bool
    {
        return $user->estAdmin()
            || ($user->estExpedition() && $pv->televerse_par === $user->id);
    }

    /** RG17 : supprimer. */
    public function delete(User $user, ProcesVerbal $pv): bool
    {
        return $this->update($user, $pv);
    }
}
