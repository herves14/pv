{{--
|--------------------------------------------------------------------------
| Liste des années académiques (UC04)
|--------------------------------------------------------------------------
--}}
@extends('layouts.app')

@section('titre', 'Années académiques')

@section('contenu')
    <x-en-tete-page titre="Années académiques">
        <a class="bouton" href="{{ route('admin.annees.create') }}">+ Ajouter une année</a>
    </x-en-tete-page>

    <div class="tableau">
        <table>
            <thead>
                <tr><th scope="col">Année</th><th scope="col">PV</th><th scope="col">Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($annees as $annee)
                    <tr>
                        <td><strong>{{ $annee->libelle }}</strong></td>
                        <td>{{ $annee->proces_verbaux_count }}</td>
                        <td class="tableau__actions">
                            <a href="{{ route('admin.annees.edit', $annee) }}">Modifier</a>
                            @if ($annee->proces_verbaux_count === 0)
                                <form method="POST" action="{{ route('admin.annees.destroy', $annee) }}" data-confirmer="Supprimer l'année {{ $annee->libelle }} ?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bouton--lien bouton--danger">Supprimer</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3">Aucune année académique.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
