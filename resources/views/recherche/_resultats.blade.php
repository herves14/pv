{{--
|--------------------------------------------------------------------------
| Fragment : liste des résultats de recherche
|--------------------------------------------------------------------------
| Inclus par recherche/index, et renvoyé SEUL (?partiel=1) au JavaScript
| de la barre de recherche, qui l'insère sous le champ.
| Variables : $texte, $resultats, $limite, $tropCourt.
--}}
@if ($tropCourt)
    <p class="recherche__message">Tapez au moins 2 caractères.</p>
@elseif ($texte !== '')
    @if ($resultats->isEmpty())
        <p class="recherche__message">Aucun PV ne correspond à « {{ $texte }} ».</p>
    @else
        <p class="recherche__message">
            {{ $resultats->count() }} résultat(s) pour « {{ $texte }} »
            @if ($resultats->count() >= $limite) (les {{ $limite }} plus récents ; précisez la recherche) @endif
        </p>
        <div class="liste-pv">
            @foreach ($resultats as $pv)
                <x-carte-pv :pv="$pv" />
            @endforeach
        </div>
    @endif
@endif
