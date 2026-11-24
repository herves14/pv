{{--
|--------------------------------------------------------------------------
| Ajouter un PV déjà numérisé (UC05) — maquettes écrans 5 et 7
|--------------------------------------------------------------------------
| Ouvert aux trois rôles ; le chef est limité à sa filière (RG22).
| Contrôleur : ProcesVerbalController@create / @store.
| enctype="multipart/form-data" : obligatoire pour envoyer un fichier.
--}}
@extends('layouts.app')

@section('titre', 'Ajouter un PV')

@section('contenu')
    <x-en-tete-page titre="Ajouter un PV" sous-titre="Classez le PV, puis joignez le PDF numérisé (20 Mo au maximum).">
        <a class="bouton bouton--secondaire" href="{{ route('pv.scanner') }}">Scanner plutôt avec la caméra</a>
    </x-en-tete-page>

    <form method="POST" action="{{ route('pv.store') }}" enctype="multipart/form-data" class="formulaire">
        @csrf

        @include('pv._champs-classement')

        <x-champ nom="fichier" libelle="Fichier PDF" aide="Format PDF uniquement, 20 Mo au maximum.">
            <input id="fichier" name="fichier" type="file" accept="application/pdf,.pdf" required
                   data-taille-max="{{ App\Http\Requests\Pv\StoreProcesVerbalRequest::TAILLE_MAX_KO * 1024 }}">
        </x-champ>

        <div class="formulaire__actions">
            <button type="submit" class="bouton" data-envoi>Archiver le PV</button>
            <a href="{{ route('accueil') }}" class="bouton bouton--secondaire">Annuler</a>
        </div>
    </form>
@endsection
