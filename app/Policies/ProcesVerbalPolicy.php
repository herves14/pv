<?php

namespace App\Policies;

use App\Models\ProcesVerbal;
use App\Models\Promotion;
use App\Models\User;

/**
 * Droits sur un PV précis (matrice de la section 6 de la modélisation).
 *
 * Utilisation : Gate::authorize('view', $pv) dans un contrôleur,
 *
 * @can('update', $pv) dans une vue, $user->can('delete', $pv) ailleurs.
 */
class ProcesVerbalPolicy
{
    /** Ouvrir / télécharger : RG18 (admin), RG19 (chef : sa filière), RG20 (expédition : ses envois). */
    public function view(User $user, ProcesVerbal $pv): bool
    {
        if ($user->estAdmin()) {
            return true;
        }

        if ($user->estChef()) {
            return $user->dirige($pv->promotion->filiere_id);
        }

        return $pv->televerse_par === $user->id;
    }

    /** Afficher le bouton « Ajouter un PV » : les 3 rôles (RG22 pour le chef). */
    public function create(User $user): bool
    {
        return $user->actif;
    }

    /**
     * Téléverser un PV dans CETTE promotion (RG22).
     * Admin et expédition : partout. Chef : seulement dans sa filière.
     * Appel : Gate::authorize('televerserDans', [ProcesVerbal::class, $promotion]).
     */
    public function televerserDans(User $user, Promotion $promotion): bool
    {
        if ($user->estAdmin() || $user->estExpedition()) {
            return true;
        }

        return $user->dirige($promotion->filiere_id);
    }

    /** Remplacer le fichier (RG17) : admin partout ; chef et expédition, leurs propres envois. */
    public function update(User $user, ProcesVerbal $pv): bool
    {
        if ($user->estAdmin()) {
            return true;
        }

        if ($pv->televerse_par !== $user->id) {
            return false;
        }

        // Un chef qui a changé de filière perd la main sur ses anciens envois.
        return $user->estExpedition() || $user->dirige($pv->promotion->filiere_id);
    }

    /** Supprimer : mêmes droits que remplacer (RG17). */
    public function delete(User $user, ProcesVerbal $pv): bool
    {
        return $this->update($user, $pv);
    }
}
