<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * UC01 : se connecter et se déconnecter.
 *
 * La limite de 5 tentatives par minute est posée sur la route (throttle:5,1).
 * Après la connexion, on envoie vers « / » : AccueilController redirige
 * ensuite chaque rôle vers son espace (RG21).
 */
class ConnexionController extends Controller
{
    /** Affiche le formulaire de connexion. */
    public function formulaire(): View
    {
        return view('auth.connexion');
    }

    /** Vérifie l'e-mail et le mot de passe, puis ouvre la session. */
    public function connecter(Request $request): RedirectResponse
    {
        $identifiants = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Saisissez votre adresse e-mail.',
            'email.email' => 'Cette adresse e-mail n\'est pas valide.',
            'password.required' => 'Saisissez votre mot de passe.',
        ]);

        // RG05 : un compte désactivé ne peut pas se connecter.
        // Ajouter « actif = true » aux identifiants fait échouer la tentative.
        $identifiants['actif'] = true;

        if (! Auth::attempt($identifiants, $request->boolean('remember'))) {
            // Message volontairement vague : on ne dit pas si l'e-mail existe.
            throw ValidationException::withMessages([
                'email' => 'Identifiants incorrects ou compte désactivé.',
            ]);
        }

        // Nouvel identifiant de session : protège contre la fixation de session.
        $request->session()->regenerate();

        return redirect()->intended(route('accueil'));
    }

    /** Ferme la session et renvoie vers la page de connexion. */
    public function deconnecter(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('succes', 'Vous êtes déconnecté.');
    }
}
