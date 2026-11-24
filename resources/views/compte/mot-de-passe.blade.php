{{--
|--------------------------------------------------------------------------
| Changer son mot de passe (UC02) — les trois rôles
|--------------------------------------------------------------------------
| Contrôleur : Compte\MotDePasseController. Validation : MotDePasseRequest.
--}}
@extends('layouts.app')

@section('titre', 'Mon mot de passe')

@section('contenu')
    <x-en-tete-page titre="Mon mot de passe" sous-titre="Au moins 8 caractères, différent de l'actuel." />

    <form method="POST" action="{{ route('compte.mot-de-passe.update') }}" class="formulaire formulaire--etroit">
        @csrf
        @method('PUT')

        <x-champ nom="mot_de_passe_actuel" libelle="Mot de passe actuel">
            <input id="mot_de_passe_actuel" name="mot_de_passe_actuel" type="password" autocomplete="current-password" required>
        </x-champ>

        <x-champ nom="password" libelle="Nouveau mot de passe">
            <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
        </x-champ>

        <x-champ nom="password_confirmation" libelle="Confirmer le nouveau mot de passe">
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        </x-champ>

        <div class="formulaire__actions">
            <button type="submit" class="bouton">Enregistrer</button>
            <a href="{{ route('accueil') }}" class="bouton bouton--secondaire">Annuler</a>
        </div>
    </form>
@endsection
