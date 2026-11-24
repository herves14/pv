{{--
|--------------------------------------------------------------------------
| Page de connexion (UC01) — maquette écran 1
|--------------------------------------------------------------------------
| Contrôleur : Auth\ConnexionController. Après connexion, chaque rôle est
| redirigé vers son espace (RG21). Limite : 5 tentatives par minute.
--}}
@extends('layouts.invite')

@section('titre', 'Connexion')

@section('contenu')
    <div class="carte carte--connexion">
        <div class="connexion__marque">
            <span class="logo__pastille logo__pastille--grande" aria-hidden="true">PV</span>
            <h1 class="connexion__titre">{{ config('app.name') }}</h1>
            <p class="connexion__sous-titre">ENEAM · Procès-verbaux de délibération</p>
        </div>

        @include('partials.alertes')

        <form method="POST" action="{{ route('login.tentative') }}" class="formulaire" novalidate>
            @csrf

            <x-champ nom="email" libelle="Adresse e-mail">
                <input id="email" name="email" type="email" value="{{ old('email') }}"
                       autocomplete="username" required autofocus
                       @error('email') aria-invalid="true" aria-describedby="email-erreur" @enderror>
            </x-champ>

            <x-champ nom="password" libelle="Mot de passe">
                <input id="password" name="password" type="password" autocomplete="current-password" required
                       @error('password') aria-invalid="true" aria-describedby="password-erreur" @enderror>
            </x-champ>

            <label class="case">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                <span>Rester connecté sur cet appareil</span>
            </label>

            <button type="submit" class="bouton bouton--large">Se connecter</button>
        </form>

        <p class="connexion__aide">Mot de passe oublié ? Contactez la direction, qui gère les comptes.</p>
    </div>
@endsection
