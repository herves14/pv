{{--
|--------------------------------------------------------------------------
| Modifier un PV / remplacer son fichier (UC06)
|--------------------------------------------------------------------------
| Admin : tous les PV. Chef et expédition : leurs propres envois (RG17).
| Le classement n'est pas modifiable ici : pour reclasser, supprimer puis
| ajouter à nouveau (évite de « déplacer » un PV dans une autre filière).
| Contrôleur : ProcesVerbalController@edit / @update.
--}}
@extends('layouts.app')

@section('titre', 'Modifier le PV')

@section('contenu')
    <x-en-tete-page :titre="'Modifier le '.$pv->titre"
                    :sous-titre="$pv->promotion->filiere->code.' · '.$pv->promotion->libelle.' · '.$pv->anneeAcademique->libelle">
        <a class="bouton bouton--secondaire" href="{{ route('pv.fichier', $pv) }}" target="_blank" rel="noopener">Voir le PDF actuel</a>
    </x-en-tete-page>

    <form method="POST" action="{{ route('pv.update', $pv) }}" enctype="multipart/form-data" class="formulaire">
        @csrf
        @method('PUT')

        <div class="groupe-champs">
            <x-champ nom="date_deliberation" libelle="Date de délibération" :facultatif="true">
                <input id="date_deliberation" name="date_deliberation" type="date"
                       value="{{ old('date_deliberation', $pv->date_deliberation?->toDateString()) }}" max="{{ now()->toDateString() }}">
            </x-champ>

            <x-champ nom="observation" libelle="Observation" :facultatif="true">
                <input id="observation" name="observation" type="text" maxlength="1000"
                       value="{{ old('observation', $pv->observation) }}">
            </x-champ>
        </div>

        <x-champ nom="fichier" libelle="Remplacer le fichier PDF" :facultatif="true"
                 :aide="'Fichier actuel : '.$pv->fichier_nom_original.' ('.$pv->taille_lisible.'). Laissez vide pour le garder.'">
            <input id="fichier" name="fichier" type="file" accept="application/pdf,.pdf"
                   data-taille-max="{{ App\Http\Requests\Pv\StoreProcesVerbalRequest::TAILLE_MAX_KO * 1024 }}">
        </x-champ>

        <div class="formulaire__actions">
            <button type="submit" class="bouton" data-envoi>Enregistrer</button>
            <a href="{{ url()->previous(route('accueil')) }}" class="bouton bouton--secondaire">Annuler</a>
        </div>
    </form>
@endsection
