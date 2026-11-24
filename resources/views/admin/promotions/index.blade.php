{{--
|--------------------------------------------------------------------------
| Liste des promotions, filtrable par filière (UC04)
|--------------------------------------------------------------------------
| Contrôleur : Admin\PromotionController@index.
--}}
@extends('layouts.app')

@section('titre', 'Promotions')

@section('contenu')
    <x-en-tete-page titre="Promotions">
        <a class="bouton" href="{{ route('admin.promotions.create', ['filiere' => $filiereFiltre]) }}">+ Ajouter une promotion</a>
    </x-en-tete-page>

    <form method="GET" class="filtres" aria-label="Filtrer les promotions">
        <label for="filiere" class="filtres__libelle">Filière</label>
        <select id="filiere" name="filiere" data-envoi-auto>
            <option value="">Toutes les filières</option>
            @foreach ($filieres as $filiere)
                <option value="{{ $filiere->id }}" @selected($filiereFiltre === $filiere->id)>{{ $filiere->code }}</option>
            @endforeach
        </select>
        <noscript><button type="submit" class="bouton bouton--secondaire">Filtrer</button></noscript>
    </form>

    <div class="tableau">
        <table>
            <thead>
                <tr><th scope="col">Filière</th><th scope="col">Promotion</th><th scope="col">PV</th><th scope="col">Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($promotions as $promotion)
                    <tr>
                        <td><strong>{{ $promotion->filiere->code }}</strong></td>
                        <td>{{ $promotion->libelle }}</td>
                        <td>{{ $promotion->proces_verbaux_count }}</td>
                        <td class="tableau__actions">
                            <a href="{{ route('admin.promotions.edit', $promotion) }}">Modifier</a>
                            @if ($promotion->proces_verbaux_count === 0)
                                <form method="POST" action="{{ route('admin.promotions.destroy', $promotion) }}" data-confirmer="Supprimer la promotion {{ $promotion->libelle }} ({{ $promotion->filiere->code }}) ?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bouton--lien bouton--danger">Supprimer</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">Aucune promotion.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $promotions->links('partials.pagination') }}
@endsection
