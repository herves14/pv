{{--
|--------------------------------------------------------------------------
| Liste des semestres, dans l'ordre d'affichage (UC04)
|--------------------------------------------------------------------------
--}}
@extends('layouts.app')

@section('titre', 'Semestres')

@section('contenu')
    <x-en-tete-page titre="Semestres">
        <a class="bouton" href="{{ route('admin.semestres.create') }}">+ Ajouter un semestre</a>
    </x-en-tete-page>

    <div class="tableau">
        <table>
            <thead>
                <tr><th scope="col">Ordre</th><th scope="col">Code</th><th scope="col">Libellé</th><th scope="col">PV</th><th scope="col">Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($semestres as $semestre)
                    <tr>
                        <td>{{ $semestre->ordre }}</td>
                        <td><strong>{{ $semestre->code }}</strong></td>
                        <td>{{ $semestre->libelle }}</td>
                        <td>{{ $semestre->proces_verbaux_count }}</td>
                        <td class="tableau__actions">
                            <a href="{{ route('admin.semestres.edit', $semestre) }}">Modifier</a>
                            @if ($semestre->proces_verbaux_count === 0)
                                <form method="POST" action="{{ route('admin.semestres.destroy', $semestre) }}" data-confirmer="Supprimer le semestre {{ $semestre->code }} ?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bouton--lien bouton--danger">Supprimer</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Aucun semestre.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
