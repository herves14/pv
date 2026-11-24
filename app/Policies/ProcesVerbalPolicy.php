<?php

namespace App\Policies;

use App\Models\ProcesVerbal;
use App\Models\Promotion;
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
            return $this->estDeSaFiliere($user, $pv->promotion);
        }

        return $pv->televerse_par === $user->id;
    }

    /** Afficher le bouton « Ajouter un PV » : admin, expédition et chef (RG22). */
    public function create(User $user): bool
    {
        return $user->actif;
    }

    /**
     * Téléverser un PV pour CETTE promotion. À appeler dans le contrôleur :
     * Gate::authorize('televerserDans', [ProcesVerbal::class, $promotion]).
     * RG22 : un chef ne peut ajouter que dans une promotion de sa filière.
     */
    public function televerserDans(User $user, Promotion $promotion): bool
    {
        if ($user->estAdmin() || $user->estExpedition()) {
            return true;
        }

        return $this->estDeSaFiliere($user, $promotion);
    }

    /** RG17 : remplacer le fichier (admin : tout ; chef et expédition : leurs envois). */
    public function update(User $user, ProcesVerbal $pv): bool
    {
        if ($user->estAdmin()) {
            return true;
        }

        if ($pv->televerse_par !== $user->id) {
            return false;
        }

        // Un chef qui aurait changé de filière ne garde pas la main sur ses anciens envois.
        return $user->estExpedition() || $this->estDeSaFiliere($user, $pv->promotion);
    }

    /** RG17 : supprimer. */
    public function delete(User $user, ProcesVerbal $pv): bool
    {
        return $this->update($user, $pv);
    }

    private function estDeSaFiliere(User $user, Promotion $promotion): bool
    {
        return $user->estChef()
            && $user->filiere_id !== null
            && $promotion->filiere_id === $user->filiere_id;
    }
}
