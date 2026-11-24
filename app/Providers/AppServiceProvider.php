<?php

namespace App\Providers;

use App\View\Composers\MenuComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Configuration générale chargée au démarrage de l'application.
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Le menu hamburger reçoit ses liens (selon le rôle) depuis MenuComposer.
        View::composer('partials.menu', MenuComposer::class);
    }
}
