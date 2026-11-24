{{--
|--------------------------------------------------------------------------
| Ajouter ou modifier une filière (RG07, RG23)
|--------------------------------------------------------------------------
| Validation : Admin\FiliereRequest (code en majuscules, unique).
--}}
@extends('layouts.app')

@php($creation = ! $filiere->exists)

@section('titre', $creation ? 'Ajouter une filière' : 'Modifier la filière')

@section('contenu')
    @include('partials.fil-ariane', ['etapes' => [
        ['libelle' => 'Filières', 'url' => route('admin.filieres.index')],
        ['libelle' => $creation ? 'Ajouter' : $filiere->code],
    ]])

    <x-en-tete-page :titre="$creation ? 'Ajouter une filière' : 'Modifier la filière '.$filiere->code" />

    <form method="POST" class="formulaire formulaire--etroit"
          action="{{ $creation ? route('admin.filieres.store') : route('admin.filieres.update', $filiere) }}">
        @csrf
        @unless ($creation) @method('PUT') @endunless

        <x-champ nom="code" libelle="Code" aide="Sigle court en majuscules, ex. GRH, STAT.">
            <input id="code" name="code" type="text" value="{{ old('code', $filiere->code) }}" maxlength="20" required
                   autocapitalize="characters" class="champ--majuscules">
        </x-champ>

        <x-champ nom="libelle" libelle="Nom complet">
            <input id="libelle" name="libelle" type="text" value="{{ old('libelle', $filiere->libelle) }}" maxlength="150" required
                   placeholder="Ex. : Gestion des ressources humaines">
        </x-champ>

        <div class="formulaire__actions">
            <button type="submit" class="bouton">{{ $creation ? 'Ajouter la filière' : 'Enregistrer' }}</button>
            <a href="{{ route('admin.filieres.index') }}" class="bouton bouton--secondaire">Annuler</a>
        </div>
    </form>
@endsection
