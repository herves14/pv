<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use App\Models\ProcesVerbal;
use App\Models\User;
use Illuminate\View\View;

/**
 * Tableau de bord de l'administrateur (/admin) : chiffres clés,
 * état des filières (chef présent ou non) et derniers PV archivés.
 * Accès : middleware role:admin (routes/web.php).
 */
class TableauDeBordController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.tableau-de-bord', [
            'nbPv' => ProcesVerbal::count(),
            'nbFilieres' => Filiere::count(),
            'nbComptesActifs' => User::actifs()->count(),
            'filieres' => Filiere::parCode()->withCount('procesVerbaux')->with('chefs')->get(),
            'derniersPv' => ProcesVerbal::avecClassement()->latest()->limit(5)->get(),
        ]);
    }
}
