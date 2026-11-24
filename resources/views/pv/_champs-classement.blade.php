{{--
|--------------------------------------------------------------------------
| Champs de classement d'un PV, partagés par « Ajouter » et « Scanner »
|--------------------------------------------------------------------------
| Filière -> promotion -> année -> semestre -> session -> date.
|
| Filière :
|   - chef de département : affichée mais NON modifiable (RG22). Il n'y a
|     d'ailleurs qu'une filière dans $filieres. Le serveur revérifie de toute
|     façon la promotion envoyée (StoreProcesVerbalRequest::authorize).
|   - admin / expédition : liste déroulante. Elle n'est pas envoyée au
|     serveur (pas d'attribut name) : elle sert seulement à filtrer la liste
|     des promotions (public/js/app.js, data-filtre-promotions).
| Promotion : une liste groupée par filière (<optgroup>), utilisable même
|   sans JavaScript.
--}}
@php
    // Promotion à présélectionner : saisie précédente, sinon lien d'origine (?promotion=)
    $promotionSelectionnee = (int) old('promotion_id', $promotionChoisie);
    $filiereSelectionnee = $filiereVerrouillee?->id
        ?? $filieres->first(fn ($f) => $f->promotions->contains('id', $promotionSelectionnee))?->id;
@endphp

<div class="groupe-champs">
    @if ($filiereVerrouillee)
        <div class="champ">
            <span class="champ__libelle">Filière</span>
            <p class="champ__verrouille">{{ $filiereVerrouillee->nom_complet }}</p>
            <p class="champ__aide">Vous ne pouvez ajouter des PV que dans votre filière.</p>
        </div>
    @else
        <x-champ nom="filiere" libelle="Filière" id-champ="filiere">
            <select id="filiere" data-filtre-promotions="promotion_id">
                <option value="">Toutes les filières</option>
                @foreach ($filieres as $filiere)
                    <option value="{{ $filiere->id }}" @selected($filiereSelectionnee === $filiere->id)>{{ $filiere->nom_complet }}</option>
                @endforeach
            </select>
        </x-champ>
    @endif

    <x-champ nom="promotion_id" libelle="Promotion">
        <select id="promotion_id" name="promotion_id" required>
            <option value="">Choisir une promotion</option>
            @foreach ($filieres as $filiere)
                @if ($filiere->promotions->isNotEmpty())
                    <optgroup label="{{ $filiere->code }}" data-filiere="{{ $filiere->id }}">
                        @foreach ($filiere->promotions as $promotion)
                            <option value="{{ $promotion->id }}" @selected($promotionSelectionnee === $promotion->id)>{{ $promotion->libelle }}</option>
                        @endforeach
                    </optgroup>
                @endif
            @endforeach
        </select>
    </x-champ>

    <x-champ nom="annee_academique_id" libelle="Année académique">
        <select id="annee_academique_id" name="annee_academique_id" required>
            <option value="">Choisir une année</option>
            @foreach ($annees as $annee)
                <option value="{{ $annee->id }}" @selected((int) old('annee_academique_id', $anneeChoisie) === $annee->id)>{{ $annee->libelle }}</option>
            @endforeach
        </select>
    </x-champ>

    <x-champ nom="semestre_id" libelle="Semestre">
        <select id="semestre_id" name="semestre_id" required>
            <option value="">Choisir un semestre</option>
            @foreach ($semestres as $semestre)
                <option value="{{ $semestre->id }}" @selected((int) old('semestre_id') === $semestre->id)>{{ $semestre->libelle }}</option>
            @endforeach
        </select>
    </x-champ>
</div>

{{-- Session : boutons radio, « normale » cochée par défaut --}}
<fieldset @class(['champ', 'champ--erreur' => $errors->has('session')])>
    <legend class="champ__libelle">Session</legend>
    <div class="choix">
        @foreach ($sessions as $valeur => $libelle)
            <label class="choix__option">
                <input type="radio" name="session" value="{{ $valeur }}" @checked(old('session', 'normale') === $valeur)>
                <span>{{ $libelle }}</span>
            </label>
        @endforeach
    </div>
    @error('session')<p class="champ__erreur">{{ $message }}</p>@enderror
</fieldset>

<div class="groupe-champs">
    <x-champ nom="date_deliberation" libelle="Date de délibération" :facultatif="true">
        <input id="date_deliberation" name="date_deliberation" type="date" value="{{ old('date_deliberation') }}" max="{{ now()->toDateString() }}">
    </x-champ>

    <x-champ nom="observation" libelle="Observation" :facultatif="true">
        <input id="observation" name="observation" type="text" value="{{ old('observation') }}" maxlength="1000"
               placeholder="Ex. : pages 3 et 4 peu lisibles sur l'original">
    </x-champ>
</div>
