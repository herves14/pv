<?php

namespace App\Services;

use App\Models\ProcesVerbal;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Toute la logique d'enregistrement des fichiers PV, en un seul endroit.
 *
 * Pourquoi un service ? Le contrôleur reste court (il reçoit la requête et
 * renvoie une page), et la règle « fichier + ligne en base, tout ou rien »
 * n'est écrite qu'une fois, pour le téléversement comme pour le scan.
 *
 * Stockage (RG14) : disque « local » = storage/app/private, jamais accessible
 * directement depuis le web. Chemin : pv/<CODE_FILIERE>/<uuid>.pdf
 */
class GestionPvService
{
    private const DISQUE = 'local';

    /**
     * Crée un PV : enregistre le PDF puis la ligne en base.
     * Si la base refuse (ex. doublon), le fichier déjà écrit est supprimé.
     *
     * @param  array  $donnees  champs validés : promotion_id, annee_academique_id,
     *                          semestre_id, session, date_deliberation, observation
     */
    public function creer(array $donnees, UploadedFile $fichier, User $auteur): ProcesVerbal
    {
        $promotion = Promotion::with('filiere')->findOrFail($donnees['promotion_id']);
        $chemin = $this->stocker($fichier, $promotion);

        try {
            return DB::transaction(fn () => ProcesVerbal::create([
                'promotion_id' => $promotion->id,
                'annee_academique_id' => $donnees['annee_academique_id'],
                'semestre_id' => $donnees['semestre_id'],
                'session' => $donnees['session'],
                'date_deliberation' => $donnees['date_deliberation'] ?? null,
                'observation' => $donnees['observation'] ?? null,
                'fichier_chemin' => $chemin,
                'fichier_nom_original' => $this->nomOriginal($fichier),
                'fichier_taille' => $fichier->getSize(),
                'televerse_par' => $auteur->id,
            ]));
        } catch (Throwable $erreur) {
            Storage::disk(self::DISQUE)->delete($chemin);
            throw $erreur;
        }
    }

    /**
     * Met à jour un PV (date, observation) et remplace son PDF si un nouveau est fourni.
     * L'ancien fichier n'est supprimé qu'APRÈS l'enregistrement réussi du nouveau.
     */
    public function modifier(ProcesVerbal $pv, array $donnees, ?UploadedFile $nouveauFichier): ProcesVerbal
    {
        $ancienChemin = null;

        if ($nouveauFichier !== null) {
            $ancienChemin = $pv->fichier_chemin;
            $pv->fichier_chemin = $this->stocker($nouveauFichier, $pv->promotion);
            $pv->fichier_nom_original = $this->nomOriginal($nouveauFichier);
            $pv->fichier_taille = $nouveauFichier->getSize();
        }

        $pv->date_deliberation = $donnees['date_deliberation'] ?? null;
        $pv->observation = $donnees['observation'] ?? null;
        $pv->save();

        if ($ancienChemin !== null) {
            Storage::disk(self::DISQUE)->delete($ancienChemin);
        }

        return $pv;
    }

    /** Supprime la ligne en base puis le fichier (RG17). */
    public function supprimer(ProcesVerbal $pv): void
    {
        $chemin = $pv->fichier_chemin;
        $pv->delete();
        Storage::disk(self::DISQUE)->delete($chemin);
    }

    /** Range le PDF sous un nom aléatoire, dans le dossier de la filière. */
    private function stocker(UploadedFile $fichier, Promotion $promotion): string
    {
        $dossier = 'pv/'.Str::slug($promotion->filiere->code);

        return $fichier->storeAs($dossier, Str::uuid().'.pdf', self::DISQUE);
    }

    /** Nom du fichier sur l'ordinateur de l'agent, nettoyé et limité à 255 caractères. */
    private function nomOriginal(UploadedFile $fichier): string
    {
        $nom = Str::limit(basename($fichier->getClientOriginalName()), 240, '');

        return $nom !== '' ? $nom : 'pv.pdf';
    }
}
