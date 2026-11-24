<?php

namespace App\Http\Controllers;

use App\Models\ProcesVerbal;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * UC11 : recherche intelligente.
 *
 * Exemple : « GRH 2023 S3 » trouve les PV dont la filière, l'année et le
 * semestre contiennent ces trois mots. On filtre TOUJOURS d'abord par les
 * droits (visiblesPar), puis par le texte (RG25) : un chef ne trouve donc
 * jamais un PV d'une autre filière.
 *
 * Deux réponses possibles :
 *  - page complète (/recherche?q=...) : sans JavaScript ou lien partagé ;
 *  - fragment HTML (?partiel=1) : le JavaScript remplace la liste en direct.
 */
class RechercheController extends Controller
{
    /** Nombre maximum de résultats affichés. */
    private const LIMITE = 20;

    /** En dessous de 2 caractères, la recherche renverrait presque tout. */
    private const LONGUEUR_MIN = 2;

    public function __invoke(Request $request): View
    {
        $texte = trim((string) $request->query('q', ''));

        $resultats = mb_strlen($texte) >= self::LONGUEUR_MIN
            ? $this->chercher($request, $texte)
            : new Collection;

        $vue = $request->boolean('partiel') ? 'recherche._resultats' : 'recherche.index';

        return view($vue, [
            'texte' => $texte,
            'resultats' => $resultats,
            'limite' => self::LIMITE,
            'tropCourt' => $texte !== '' && mb_strlen($texte) < self::LONGUEUR_MIN,
        ]);
    }

    private function chercher(Request $request, string $texte): Collection
    {
        return ProcesVerbal::visiblesPar($request->user()) // 1. les droits
            ->recherche($texte)                             // 2. le texte
            ->avecClassement()
            ->latest()
            ->limit(self::LIMITE)
            ->get();
    }
}
