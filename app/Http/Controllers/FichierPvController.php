<?php

namespace App\Http\Controllers;

use App\Models\ProcesVerbal;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Seule porte d'accès aux PDF (UC08).
 *
 * Les fichiers sont sur le disque privé « local » (storage/app/private) et
 * jamais dans public/ : impossible de les ouvrir en devinant une adresse.
 * Chaque ouverture repasse par ProcesVerbalPolicy::view (RG18 à RG20).
 */
class FichierPvController extends Controller
{
    /** Affiche le PDF dans la visionneuse du navigateur (en-tête « inline »). */
    public function afficher(ProcesVerbal $pv): StreamedResponse
    {
        Gate::authorize('view', $pv);
        abort_unless(Storage::disk('local')->exists($pv->fichier_chemin), 404, 'Le fichier de ce PV est introuvable.');

        return Storage::disk('local')->response(
            $pv->fichier_chemin,
            $pv->fichier_nom_original,
            ['Content-Type' => 'application/pdf'],
            'inline'
        );
    }

    /** Force le téléchargement du PDF (en-tête « attachment »). */
    public function telecharger(ProcesVerbal $pv): StreamedResponse
    {
        Gate::authorize('view', $pv);
        abort_unless(Storage::disk('local')->exists($pv->fichier_chemin), 404, 'Le fichier de ce PV est introuvable.');

        return Storage::disk('local')->download($pv->fichier_chemin, $pv->fichier_nom_original);
    }
}
