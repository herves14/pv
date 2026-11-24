{{--
|--------------------------------------------------------------------------
| Mise en page des pages connectées
|--------------------------------------------------------------------------
| Squelette commun à toutes les pages après connexion. Chaque morceau est
| dans son propre fichier (resources/views/partials) :
|   head          -> <head> : titre, encodage, feuille de style
|   entete        -> barre du haut : bouton hamburger, logo, rôle
|     menu        -> panneau du menu hamburger (inclus par l'en-tête)
|   alertes       -> messages de succès / d'erreur après une action
|   pied-de-page  -> bas de page
|   scripts       -> fichiers JavaScript
|
| Une page l'utilise ainsi :
|   @extends('layouts.app')
|   @section('titre', 'Ma filière')
|   @section('contenu') ... @endsection
--}}
<!doctype html>
<html lang="fr">
<head>
    @include('partials.head')
</head>
<body>
    {{-- Lien caché, visible au clavier : saute directement au contenu (accessibilité) --}}
    <a class="lien-evitement" href="#contenu">Aller au contenu</a>

    @include('partials.entete')

    <main id="contenu" class="page" tabindex="-1">
        @include('partials.alertes')

        @yield('contenu')
    </main>

    @include('partials.pied-de-page')

    @include('partials.scripts')
</body>
</html>
