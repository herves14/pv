{{--
|--------------------------------------------------------------------------
| Ajouter ou modifier un semestre (RG10)
|--------------------------------------------------------------------------
--}}
@extends('layouts.app')

@php($creation = ! $semestre->exists)

@section('titre', $creation ? 'Ajouter un semestre' : 'Modifier le semestre')

@section('contenu')
    @include('partials.fil-ariane', ['etapes' => [
        ['libelle' => 'Semestres', 'url' => route('admin.semestres.index')],
        ['libelle' => $creation ? 'Ajouter' : $semestre->code],
    ]])

    <x-en-tete-page :titre="$creation ? 'Ajouter un semestre' : 'Modifier le semestre '.$semestre->code" />

    <form method="POST" class="formulaire formulaire--etroit"
          action="{{ $creation ? route('admin.semestres.store') : route('admin.semestres.update', $semestre) }}">
        @csrf
        @unless ($creation) @method('PUT') @endunless

        <div class="groupe-champs">
            <x-champ nom="code" libelle="Code" aide="Ex. : S7">
                <input id="code" name="code" type="text" value="{{ old('code', $semestre->code) }}" maxlength="3" required class="champ--majuscules">
            </x-champ>

            <x-champ nom="ordre" libelle="Ordre d'affichage">
                <input id="ordre" name="ordre" type="number" min="1" max="20" value="{{ old('ordre', $semestre->ordre) }}" required>
            </x-champ>
        </div>

        <x-champ nom="libelle" libelle="Libellé" aide="Ex. : Semestre 7">
            <input id="libelle" name="libelle" type="text" value="{{ old('libelle', $semestre->libelle) }}" maxlength="50" required>
        </x-champ>

        <div class="formulaire__actions">
            <button type="submit" class="bouton">{{ $creation ? 'Ajouter le semestre' : 'Enregistrer' }}</button>
            <a href="{{ route('admin.semestres.index') }}" class="bouton bouton--secondaire">Annuler</a>
        </div>
    </form>
@endsection
