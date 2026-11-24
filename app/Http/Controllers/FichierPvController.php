<?php

namespace App\Http\Controllers;

use App\Models\ProcesVerbal;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Seule porte d'accès aux PDF : les fichiers sont sur le disque privé
 * "local" (storage/app/private), jamais dans public/ (RG14, RG19).
 */
class FichierPvController extends Controller
{
    /** Affiche le PDF dans la visionneuse du navigateur (UC08). */
    public function afficher(ProcesVerbal $pv): StreamedResponse
    {
        Gate::authorize('view', $pv);

        return Storage::disk('local')->response(
            $pv->fichier_chemin,
            $pv->fichier_nom_original,
            ['Content-Type' => 'application/pdf'],
            'inline'
        );
    }

    /** Force le téléchargement. */
    public function telecharger(ProcesVerbal $pv): StreamedResponse
    {
        Gate::authorize('view', $pv);

        return Storage::disk('local')->download($pv->fichier_chemin, $pv->fichier_nom_original);
    }
}
