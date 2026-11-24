{{--
|--------------------------------------------------------------------------
| Liste des comptes utilisateurs (UC03)
|--------------------------------------------------------------------------
| Contrôleur : Admin\UtilisateurController@index. Filtre facultatif par rôle.
| Pas de suppression : on désactive (RG05). L'admin ne peut pas se
| désactiver lui-même (RG06) : le bouton n'apparaît pas sur sa ligne.
--}}
@extends('layouts.app')

@section('titre', 'Utilisateurs')

@section('contenu')
    <x-en-tete-page titre="Comptes utilisateurs" sous-titre="Une filière ne peut avoir qu'un seul chef actif (RG04).">
        <a class="bouton" href="{{ route('admin.utilisateurs.create') }}">+ Nouveau compte</a>
    </x-en-tete-page>

    {{-- Filtre par rôle (formulaire GET : l'adresse peut être partagée) --}}
    <form method="GET" class="filtres" aria-label="Filtrer les comptes">
        <label for="role" class="filtres__libelle">Rôle</label>
        <select id="role" name="role" data-envoi-auto>
            <option value="">Tous les rôles</option>
            @foreach ($roles as $valeur => $libelle)
                <option value="{{ $valeur }}" @selected($roleFiltre === $valeur)>{{ $libelle }}</option>
            @endforeach
        </select>
        <noscript><button type="submit" class="bouton bouton--secondaire">Filtrer</button></noscript>
    </form>

    <div class="tableau">
        <table>
            <thead>
                <tr>
                    <th scope="col">Nom</th>
                    <th scope="col">Adresse e-mail</th>
                    <th scope="col">Rôle</th>
                    <th scope="col">Filière</th>
                    <th scope="col">État</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($utilisateurs as $utilisateur)
                    <tr>
                        <td>{{ $utilisateur->name }} @if ($utilisateur->is(auth()->user()))<span class="texte-discret">(vous)</span>@endif</td>
                        <td>{{ $utilisateur->email }}</td>
                        <td>{{ $utilisateur->role->libelle() }}</td>
                        <td>{{ $utilisateur->filiere?->code ?? '—' }}</td>
                        <td><x-badge-etat :actif="$utilisateur->actif" /></td>
                        <td class="tableau__actions">
                            <a href="{{ route('admin.utilisateurs.edit', $utilisateur) }}">Modifier</a>
                            @unless ($utilisateur->is(auth()->user()))
                                <form method="POST" action="{{ route('admin.utilisateurs.activation', $utilisateur) }}"
                                      @if ($utilisateur->actif) data-confirmer="Désactiver le compte de {{ $utilisateur->name }} ? Il ne pourra plus se connecter." @endif>
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" @class(['bouton--lien', 'bouton--danger' => $utilisateur->actif])>
                                        {{ $utilisateur->actif ? 'Désactiver' : 'Réactiver' }}
                                    </button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Aucun compte ne correspond à ce filtre.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $utilisateurs->links('partials.pagination') }}
@endsection
