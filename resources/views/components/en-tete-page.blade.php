{{--
|--------------------------------------------------------------------------
| Composant <x-en-tete-page> : titre de la page + boutons d'action
|--------------------------------------------------------------------------
| <x-en-tete-page titre="Ma filière" sous-titre="GRH · Gestion des ressources humaines">
|     <a class="bouton" href="...">+ Ajouter un PV</a>
| </x-en-tete-page>
| Sur téléphone, les boutons passent sous le titre et prennent toute la largeur.
--}}
@props(['titre', 'sousTitre' => null])

<div class="en-tete-page">
    <div class="en-tete-page__textes">
        <h1 class="en-tete-page__titre">{{ $titre }}</h1>
        @if ($sousTitre)
            <p class="en-tete-page__sous-titre">{{ $sousTitre }}</p>
        @endif
    </div>

    @if ($slot->isNotEmpty())
        <div class="en-tete-page__actions">{{ $slot }}</div>
    @endif
</div>
