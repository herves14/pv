@extends('layouts.app')
@section('titre', 'Connexion')
@section('contenu')
<div class="carte" style="max-width:400px;margin:auto">
    <h1>Connexion</h1>
    <form method="POST" action="{{ url('/login') }}">
        @csrf
        <label for="email">Adresse e-mail</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
        <label for="password">Mot de passe</label>
        <input id="password" type="password" name="password" required>
        <label><input type="checkbox" name="remember"> Se souvenir de moi</label>
        @error('email') <p class="erreur">{{ $message }}</p> @enderror
        <button type="submit">Se connecter</button>
    </form>
</div>
@endsection
