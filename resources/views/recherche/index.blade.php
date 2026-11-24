{{--
|--------------------------------------------------------------------------
| Page de recherche (UC11) : /recherche?q=GRH+2023+S3
|--------------------------------------------------------------------------
| Utilisée sans JavaScript ou depuis le menu. Avec JavaScript, les résultats
| se mettent à jour pendant la frappe (même fragment recherche/_resultats).
--}}
@extends('layouts.app')

@section('titre', 'Rechercher un PV')

@section('contenu')
    <x-en-tete-page titre="Rechercher un PV" sous-titre="Seuls les PV auxquels vous avez accès sont proposés." />

    <x-barre-recherche :valeur="$texte" />

    {{-- Résultats affichés au chargement (sans JavaScript, ou lien partagé) --}}
    <div class="recherche__resultats-page" data-resultats-initiaux>
        @include('recherche._resultats')
    </div>
@endsection
