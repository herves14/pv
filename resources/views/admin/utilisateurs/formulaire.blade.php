{{--
|--------------------------------------------------------------------------
| Créer ou modifier un compte (UC03) — un seul fichier pour les deux cas
|--------------------------------------------------------------------------
| $utilisateur->exists vaut false à la création, true en modification.
| Le champ « Filière » ne concerne que le chef de département (RG03) :
| app.js l'affiche seulement quand ce rôle est choisi (data-si-role).
| Validation : Admin\UtilisateurRequest (RG03, RG04, RG06).
--}}
@extends('layouts.app')

@php($creation = ! $utilisateur->exists)

@section('titre', $creation ? 'Nouveau compte' : 'Modifier un compte')

@section('contenu')
    @include('partials.fil-ariane', ['etapes' => [
        ['libelle' => 'Utilisateurs', 'url' => route('admin.utilisateurs.index')],
        ['libelle' => $creation ? 'Nouveau compte' : $utilisateur->name],
    ]])

    <x-en-tete-page :titre="$creation ? 'Nouveau compte' : 'Modifier le compte'" />

    <form method="POST" class="formulaire formulaire--etroit"
          action="{{ $creation ? route('admin.utilisateurs.store') : route('admin.utilisateurs.update', $utilisateur) }}">
        @csrf
        @unless ($creation) @method('PUT') @endunless

        <x-champ nom="name" libelle="Nom complet">
            <input id="name" name="name" type="text" value="{{ old('name', $utilisateur->name) }}" maxlength="150" required autocomplete="off">
        </x-champ>

        <x-champ nom="email" libelle="Adresse e-mail" aide="Sert d'identifiant de connexion.">
            <input id="email" name="email" type="email" value="{{ old('email', $utilisateur->email) }}" maxlength="150" required autocomplete="off">
        </x-champ>

        <x-champ nom="role" libelle="Rôle">
            <select id="role" name="role" required data-role>
                <option value="">Choisir un rôle</option>
                @foreach ($roles as $valeur => $libelle)
                    <option value="{{ $valeur }}" @selected(old('role', $utilisateur->role?->value) === $valeur)>{{ $libelle }}</option>
                @endforeach
            </select>
        </x-champ>

        <div data-si-role="{{ App\Enums\Role::ChefDepartement->value }}">
            <x-champ nom="filiere_id" libelle="Filière dirigée" aide="Obligatoire pour un chef de département.">
                <select id="filiere_id" name="filiere_id">
                    <option value="">Choisir une filière</option>
                    @foreach ($filieres as $filiere)
                        <option value="{{ $filiere->id }}" @selected((int) old('filiere_id', $utilisateur->filiere_id) === $filiere->id)>{{ $filiere->nom_complet }}</option>
                    @endforeach
                </select>
            </x-champ>
        </div>

        <x-champ nom="password" :libelle="$creation ? 'Mot de passe provisoire' : 'Nouveau mot de passe'"
                 :facultatif="! $creation"
                 :aide="$creation ? 'Au moins 8 caractères. Communiquez-le à la personne, qui pourra le changer.' : 'Laissez vide pour garder le mot de passe actuel.'">
            <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" @required($creation)>
        </x-champ>

        <x-champ nom="password_confirmation" libelle="Confirmer le mot de passe" :facultatif="! $creation">
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @required($creation)>
        </x-champ>

        <div class="formulaire__actions">
            <button type="submit" class="bouton">{{ $creation ? 'Créer le compte' : 'Enregistrer' }}</button>
            <a href="{{ route('admin.utilisateurs.index') }}" class="bouton bouton--secondaire">Annuler</a>
        </div>
    </form>
@endsection
