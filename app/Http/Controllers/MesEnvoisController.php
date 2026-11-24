<?php

namespace App\Http\Controllers;

use App\Models\ProcesVerbal;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Espace du service d'expédition (/expedition) : la liste de SES envois
 * (RG20), du plus récent au plus ancien, 15 par page.
 */
class MesEnvoisController extends Controller
{
    public function __invoke(Request $request): View
    {
        $envois = ProcesVerbal::visiblesPar($request->user())
            ->avecClassement()
            ->latest()
            ->paginate(15);

        return view('expedition.mes-envois', compact('envois'));
    }
}
