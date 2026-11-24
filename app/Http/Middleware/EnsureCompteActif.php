<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Déconnecte immédiatement un utilisateur dont le compte a été désactivé (RG05).
 *
 * Sans ce middleware, une personne déjà connectée garderait l'accès jusqu'à
 * l'expiration de sa session, même après sa désactivation par l'admin.
 * Il est ajouté au groupe « web » (bootstrap/app.php) : il s'exécute donc à
 * chaque requête, mais ne fait rien pour un visiteur non connecté.
 */
class EnsureCompteActif
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->actif) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Votre compte a été désactivé. Contactez la direction.']);
        }

        return $next($request);
    }
}
