<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protège un espace entier : Route::middleware('role:admin').
 * Plusieurs rôles possibles : 'role:admin,expedition'. (RG21)
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless($user && $user->actif && in_array($user->role->value, $roles, true), 403);

        return $next($request);
    }
}
