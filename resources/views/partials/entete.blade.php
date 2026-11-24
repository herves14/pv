{{--
|--------------------------------------------------------------------------
| En-tête (barre du haut) des pages connectées
|--------------------------------------------------------------------------
| - Bouton hamburger : ouvre / ferme le menu (géré par public/js/app.js).
|   aria-expanded indique aux lecteurs d'écran si le menu est ouvert.
| - Logo + nom de l'application : ramène à l'accueil du rôle.
| - À droite : nom et rôle de la personne connectée (et sa filière pour un chef).
| Le panneau du menu lui-même est dans partials/menu.blade.php.
--}}
@php($utilisateur = auth()->user())

<header class="entete" data-entete>
    <div class="entete__barre">
        <div class="entete__gauche">
            <button type="button" class="bouton-menu" data-bouton-menu
                    aria-expanded="false" aria-controls="menu-principal" aria-label="Ouvrir le menu">
                {{-- Icône « trois traits » (menu fermé) --}}
                <svg class="bouton-menu__ouvrir" aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                {{-- Icône « croix » (menu ouvert) --}}
                <svg class="bouton-menu__fermer" aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>

            <a href="{{ route('accueil') }}" class="logo">
                <span class="logo__pastille" aria-hidden="true">PV</span>
                <span class="logo__nom">{{ config('app.name') }}</span>
            </a>
        </div>

        <p class="entete__utilisateur">
            <span class="entete__nom">{{ $utilisateur->name }}</span>
            <span class="entete__role">
                {{ $utilisateur->role->libelle() }}@if ($utilisateur->estChef() && $utilisateur->filiere) · {{ $utilisateur->filiere->code }}@endif
            </span>
        </p>
    </div>

    @include('partials.menu')
</header>
