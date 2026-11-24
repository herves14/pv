<?php

namespace App\View\Composers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\View\View;

/**
 * Prépare les liens du menu hamburger selon le rôle connecté.
 *
 * Pourquoi un « View Composer » ? La vue partials/menu.blade.php reste un
 * simple affichage, et la liste des liens n'est écrite qu'à un seul endroit.
 * Branché dans AppServiceProvider : View::composer('partials.menu', ...).
 *
 * Important : masquer un lien n'est PAS une protection. Chaque route est
 * protégée par le middleware « role » et par les Policies ; le menu ne fait
 * que montrer à chacun ce qu'il a le droit d'ouvrir.
 */
class MenuComposer
{
    public function compose(View $view): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        $view->with('liens', $user ? $this->liensPour($user) : []);
    }

    /**
     * Chaque lien : libellé, route, et motif de route qui le rend « actif »
     * (surligné en bleu) quand on se trouve sur une de ses pages.
     *
     * @return list<array{libelle: string, url: string, actif: bool}>
     */
    private function liensPour(User $user): array
    {
        $liens = match ($user->role) {
            Role::Admin => [
                ['Tableau de bord', 'admin.dashboard', 'admin.dashboard'],
                ['Consulter les PV', 'consultation.filieres', 'consultation.*'],
                ['Rechercher un PV', 'recherche', 'recherche'],
                ['Ajouter un PV', 'pv.create', 'pv.create'],
                ['Scanner un PV', 'pv.scanner', 'pv.scanner'],
                ['Utilisateurs', 'admin.utilisateurs.index', 'admin.utilisateurs.*'],
                ['Filières', 'admin.filieres.index', 'admin.filieres.*'],
                ['Promotions', 'admin.promotions.index', 'admin.promotions.*'],
                ['Années académiques', 'admin.annees.index', 'admin.annees.*'],
                ['Semestres', 'admin.semestres.index', 'admin.semestres.*'],
            ],
            Role::ChefDepartement => [
                ['Ma filière', 'departement.index', ['departement.index', 'consultation.*']],
                ['Rechercher un PV', 'recherche', 'recherche'],
                ['Ajouter un PV', 'pv.create', 'pv.create'],
                ['Scanner un PV', 'pv.scanner', 'pv.scanner'],
            ],
            Role::Expedition => [
                ['Mes envois', 'expedition.index', 'expedition.index'],
                ['Rechercher un PV', 'recherche', 'recherche'],
                ['Ajouter un PV', 'pv.create', 'pv.create'],
                ['Scanner un PV', 'pv.scanner', 'pv.scanner'],
            ],
        };

        // Lien commun aux trois rôles.
        $liens[] = ['Mon mot de passe', 'compte.mot-de-passe', 'compte.*'];

        return array_map(fn (array $lien) => [
            'libelle' => $lien[0],
            'url' => route($lien[1]),
            'actif' => request()->routeIs(...(array) $lien[2]),
        ], $liens);
    }
}
