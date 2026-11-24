{{--
|--------------------------------------------------------------------------
| Ajouter ou modifier une promotion (RG08 : libellé unique dans la filière)
|--------------------------------------------------------------------------
--}}
@extends('layouts.app')

@php($creation = ! $promotion->exists)

@section('titre', $creation ? 'Ajouter une promotion' : 'Modifier la promotion')

@section('contenu')
    @include('partials.fil-ariane', ['etapes' => [
        ['libelle' => 'Promotions', 'url' => route('admin.promotions.index')],
        ['libelle' => $creation ? 'Ajouter' : $promotion->libelle],
    ]])

    <x-en-tete-page :titre="$creation ? 'Ajouter une promotion' : 'Modifier la promotion'" />

    <form method="POST" class="formulaire formulaire--etroit"
          action="{{ $creation ? route('admin.promotions.store') : route('admin.promotions.update', $promotion) }}">
        @csrf
        @unless ($creation) @method('PUT') @endunless

        <x-champ nom="filiere_id" libelle="Filière">
            <select id="filiere_id" name="filiere_id" required>
                <option value="">Choisir une filière</option>
                @foreach ($filieres as $filiere)
                    <option value="{{ $filiere->id }}" @selected((int) old('filiere_id', $promotion->filiere_id) === $filiere->id)>{{ $filiere->nom_complet }}</option>
                @endforeach
            </select>
        </x-champ>

        <x-champ nom="libelle" libelle="Libellé" aide="Ex. : Promotion 2022-2025">
            <input id="libelle" name="libelle" type="text" value="{{ old('libelle', $promotion->libelle) }}" maxlength="100" required>
        </x-champ>

        <div class="formulaire__actions">
            <button type="submit" class="bouton">{{ $creation ? 'Ajouter la promotion' : 'Enregistrer' }}</button>
            <a href="{{ route('admin.promotions.index') }}" class="bouton bouton--secondaire">Annuler</a>
        </div>
    </form>
@endsection
