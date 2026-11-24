{{--
|--------------------------------------------------------------------------
| Consultation, étape 2 : les années académiques d'une promotion
|--------------------------------------------------------------------------
| Seules les années qui contiennent au moins un PV sont listées.
| Contrôleur : ConsultationController@annees.
--}}
@extends('layouts.app')

@section('titre', $promotion->libelle)

@section('contenu')
    @include('partials.fil-ariane', ['etapes' => array_values(array_filter([
        auth()->user()->estAdmin() ? ['libelle' => 'Filières', 'url' => route('consultation.filieres')] : null,
        ['libelle' => $filiere->code, 'url' => auth()->user()->estChef() ? route('departement.index') : route('consultation.promotions', $filiere)],
        ['libelle' => $promotion->libelle],
    ]))])

    <x-en-tete-page :titre="$promotion->libelle" sous-titre="Choisissez une année académique.">
        @can('televerserDans', [App\Models\ProcesVerbal::class, $promotion])
            <a class="bouton" href="{{ route('pv.create', ['promotion' => $promotion->id]) }}">+ Ajouter un PV</a>
        @endcan
    </x-en-tete-page>

    @if ($annees->isEmpty())
        <x-etat-vide titre="Aucun PV archivé pour cette promotion">
            Les PV apparaîtront ici dès qu'ils seront téléversés ou scannés.
        </x-etat-vide>
    @else
        <ul class="grille" role="list">
            @foreach ($annees as $annee)
                <li>
                    <a class="carte carte--lien" href="{{ route('consultation.semestres', [$promotion, $annee]) }}">
                        <span class="carte__titre">{{ $annee->libelle }}</span>
                        <span class="carte__meta">{{ $annee->proces_verbaux_count }} PV</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
