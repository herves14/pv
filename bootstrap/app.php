<?php

/*
|--------------------------------------------------------------------------
| Démarrage de l'application
|--------------------------------------------------------------------------
| Ce fichier configure le routage, les middlewares et la gestion des erreurs.
| Les middlewares maison sont dans app/Http/Middleware.
*/

use App\Http\Middleware\EnsureCompteActif;
use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias utilisable dans les routes : ->middleware('role:admin')
        $middleware->alias([
            'role' => EnsureRole::class,
        ]);

        // Vérifie à chaque requête que le compte connecté est toujours actif (RG05).
        $middleware->appendToGroup('web', EnsureCompteActif::class);

        // Un visiteur non connecté est envoyé vers /login ;
        // un utilisateur déjà connecté qui ouvre /login est renvoyé vers son espace.
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('accueil'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Les appels JavaScript (recherche, liste des promotions) reçoivent du JSON.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
