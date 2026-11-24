<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Après connexion : redirection vers l'espace du rôle (UC01, RG21). */
class AccueilController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->role->routeAccueil());
    }
}
