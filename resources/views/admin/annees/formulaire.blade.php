{{--
|--------------------------------------------------------------------------
| Ajouter ou modifier une année académique (RG09 : format 2023-2024)
|--------------------------------------------------------------------------
--}}
@extends('layouts.app')

@php($creation = ! $annee->exists)

@section('titre', $creation ? 'Ajouter une année' : 'Modifier l\'année')

@section('contenu')
    @include('partials.fil-ariane', ['etapes' => [
        ['libelle' => 'Années académiques', 'url' => route('admin.annees.index')],
        ['libelle' => $creation ? 'Ajouter' : $annee->libelle],
    ]])

    <x-en-tete-page :titre="$creation ? 'Ajouter une année académique' : 'Modifier l\'année '.$annee->libelle" />

    <form method="POST" class="formulaire formulaire--etroit"
          action="{{ $creation ? route('admin.annees.store') : route('admin.annees.update', $annee) }}">
        @csrf
        @unless ($creation) @method('PUT') @endunless

        <x-champ nom="libelle" libelle="Année académique" aide="Format : 2023-2024">
            <input id="libelle" name="libelle" type="text" value="{{ old('libelle', $annee->libelle) }}"
                   inputmode="numeric" pattern="\d{4}-\d{4}" maxlength="9" placeholder="2023-2024" required>
        </x-champ>

        <div class="formulaire__actions">
            <button type="submit" class="bouton">{{ $creation ? 'Ajouter l\'année' : 'Enregistrer' }}</button>
            <a href="{{ route('admin.annees.index') }}" class="bouton bouton--secondaire">Annuler</a>
        </div>
    </form>
@endsection
