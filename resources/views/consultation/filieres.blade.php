{{--
|--------------------------------------------------------------------------
| Consultation, étape 0 (admin) : choisir une filière
|--------------------------------------------------------------------------
| Contrôleur : ConsultationController@filieres. Le chef ne voit jamais cette
| page : il est redirigé directement vers sa filière.
--}}
@extends('layouts.app')

@section('titre', 'Consulter les PV')

@section('contenu')
    <x-en-tete-page titre="Consulter les PV" sous-titre="Choisissez une filière, ou recherchez directement un PV.">
        <a class="bouton bouton--secondaire" href="{{ route('pv.scanner') }}">Scanner un PV</a>
        <a class="bouton" href="{{ route('pv.create') }}">+ Ajouter un PV</a>
    </x-en-tete-page>

    <x-barre-recherche />

    @if ($filieres->isEmpty())
        <x-etat-vide titre="Aucune filière enregistrée">
            <a class="bouton" href="{{ route('admin.filieres.create') }}">+ Ajouter une filière</a>
        </x-etat-vide>
    @else
        <ul class="grille" role="list">
            @foreach ($filieres as $filiere)
                <li>
                    <a class="carte carte--lien" href="{{ route('consultation.promotions', $filiere) }}">
                        <span class="carte__code">{{ $filiere->code }}</span>
                        <span class="carte__titre">{{ $filiere->libelle }}</span>
                        <span class="carte__meta">{{ $filiere->promotions_count }} promotion(s) · {{ $filiere->proces_verbaux_count }} PV</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
