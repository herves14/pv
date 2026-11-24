{{--
|--------------------------------------------------------------------------
| Composant <x-etat-vide> : message quand une liste est vide
|--------------------------------------------------------------------------
| <x-etat-vide titre="Aucun PV pour le moment">Texte et bouton facultatifs</x-etat-vide>
--}}
@props(['titre'])

<div class="etat-vide">
    <p class="etat-vide__titre">{{ $titre }}</p>
    @if ($slot->isNotEmpty())
        <div class="etat-vide__texte">{{ $slot }}</div>
    @endif
</div>
