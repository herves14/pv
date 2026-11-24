<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Page « / » : n'affiche rien, redirige vers l'espace du rôle (UC01, RG21).
 *   admin            -> /admin        (tableau de bord)
 *   chef_departement -> /departement  (sa filière)
 *   expedition       -> /expedition   (ses envois)
 */
class AccueilController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->role->routeAccueil());
    }
}
