<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** UC01 : se connecter / se déconnecter. */
class ConnexionController extends Controller
{
    public function formulaire(): View
    {
        return view('auth.login');
    }

    public function connecter(Request $request): RedirectResponse
    {
        $identifiants = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // RG05 : un compte désactivé ne peut pas se connecter.
        $identifiants['actif'] = true;

        if (! Auth::attempt($identifiants, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Identifiants incorrects ou compte désactivé.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('accueil');
    }

    public function deconnecter(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
