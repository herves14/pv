<?php

namespace App\Http\Controllers\Compte;

use App\Http\Controllers\Controller;
use App\Http\Requests\Compte\MotDePasseRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * UC02 : chaque utilisateur change son propre mot de passe.
 * La vérification de l'ancien mot de passe est faite par MotDePasseRequest.
 */
class MotDePasseController extends Controller
{
    public function edit(): View
    {
        return view('compte.mot-de-passe');
    }

    public function update(MotDePasseRequest $request): RedirectResponse
    {
        // Le cast « hashed » du modèle User chiffre le mot de passe automatiquement.
        $request->user()->update(['password' => $request->validated('password')]);

        return redirect()->route('accueil')->with('succes', 'Votre mot de passe a été modifié.');
    }
}
