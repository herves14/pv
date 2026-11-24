{{--
|--------------------------------------------------------------------------
| Liste des filières + « Ajouter une filière » (RG23) — maquette écran 8
|--------------------------------------------------------------------------
| Contrôleur : Admin\FiliereController@index.
| « Supprimer » n'apparaît que si FilierePolicy::delete l'autorise
| (aucune promotion, RG11).
--}}
@extends('layouts.app')

@section('titre', 'Filières')

@section('contenu')
    <x-en-tete-page titre="Filières" :sous-titre="$filieres->count().' filière(s) à l\'ENEAM'">
        <a class="bouton" href="{{ route('admin.filieres.create') }}">+ Ajouter une filière</a>
    </x-en-tete-page>

    <div class="tableau">
        <table>
            <thead>
                <tr>
                    <th scope="col">Code</th>
                    <th scope="col">Nom complet</th>
                    <th scope="col">Chef actif</th>
                    <th scope="col">Promotions</th>
                    <th scope="col">PV</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($filieres as $filiere)
                    @php($chef = $filiere->chefs->firstWhere('actif', true))
                    <tr>
                        <td><strong>{{ $filiere->code }}</strong></td>
                        <td>{{ $filiere->libelle }}</td>
                        <td>{!! $chef ? e($chef->name) : '<span class="badge badge--attention">Aucun</span>' !!}</td>
                        <td><a href="{{ route('admin.promotions.index', ['filiere' => $filiere->id]) }}">{{ $filiere->promotions_count }}</a></td>
                        <td>{{ $filiere->proces_verbaux_count }}</td>
                        <td class="tableau__actions">
                            <a href="{{ route('admin.filieres.edit', $filiere) }}">Modifier</a>
                            @can('delete', $filiere)
                                <form method="POST" action="{{ route('admin.filieres.destroy', $filiere) }}" data-confirmer="Supprimer la filière {{ $filiere->code }} ?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bouton--lien bouton--danger">Supprimer</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Aucune filière. Commencez par en ajouter une.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
