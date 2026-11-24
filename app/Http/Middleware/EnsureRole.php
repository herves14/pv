<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware « role » : protège un espace entier selon le rôle (RG21).
 *
 * Utilisation dans routes/web.php :
 *   Route::middleware('role:admin')                      -> admin uniquement
 *   Route::middleware('role:admin,chef_departement')     -> admin OU chef
 *
 * Il ne décide PAS pour un PV précis (ex. « ce chef peut-il voir CE PV ? ») :
 * ce contrôle fin est fait par les Policies (app/Policies).
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Garde-fou : la route doit aussi avoir le middleware « auth ».
        if ($user === null) {
            return redirect()->guest(route('login'));
        }

        // On convertit les noms reçus (« admin »…) en valeurs de l'enum Role.
        // Une faute de frappe dans routes/web.php lève une erreur tout de suite
        // (Role::from), au lieu de bloquer silencieusement tout le monde.
        $autorises = array_map(fn (string $role) => Role::from($role), $roles);

        abort_unless(in_array($user->role, $autorises, true), 403, "Cet espace n'est pas accessible avec votre rôle.");

        return $next($request);
    }
}
