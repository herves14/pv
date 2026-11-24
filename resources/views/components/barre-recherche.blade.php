{{--
|--------------------------------------------------------------------------
| Composant <x-barre-recherche /> : recherche intelligente (UC11)
|--------------------------------------------------------------------------
| - Sans JavaScript : formulaire classique vers /recherche?q=...
| - Avec JavaScript (public/js/app.js) : les résultats s'affichent sous la
|   barre pendant la frappe (300 ms après la dernière touche), sans
|   recharger la page. Le serveur ne renvoie que les PV autorisés (RG25).
| aria-live="polite" : les lecteurs d'écran annoncent les nouveaux résultats.
--}}
@props(['valeur' => '', 'exemple' => 'Ex. : GRH 2023 S3 rattrapage'])

<form class="recherche" method="GET" action="{{ route('recherche') }}" role="search" data-recherche>
    <label class="visuellement-cache" for="recherche-q">Rechercher un PV</label>
    <div class="recherche__barre">
        <svg class="recherche__icone" aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
        <input id="recherche-q" class="recherche__champ" type="search" name="q" value="{{ $valeur }}"
               placeholder="{{ $exemple }}" autocomplete="off" minlength="2" maxlength="100">
        <button type="submit" class="bouton">Rechercher</button>
    </div>
    <p class="champ__aide">Tapez une filière, une promotion, une année, un semestre ou une session, dans n'importe quel ordre.</p>

    <div class="recherche__resultats" data-resultats aria-live="polite"></div>
</form>
