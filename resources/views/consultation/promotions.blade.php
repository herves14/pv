{{--
|--------------------------------------------------------------------------
| Consultation, étape 1 : les promotions d'une filière — maquette écran 2
|--------------------------------------------------------------------------
| C'est aussi la page d'accueil du chef de département (/departement).
| Contrôleur : ConsultationController@promotions (ou @maFiliere).
| Variables : $filiere, $promotions (avec proces_verbaux_count).
--}}
@extends('layouts.app')

@section('titre', $filiere->code)

@section('contenu')
    @if (auth()->user()->estAdmin())
        @include('partials.fil-ariane', ['etapes' => [
            ['libelle' => 'Filières', 'url' => route('consultation.filieres')],
            ['libelle' => $filiere->code],
        ]])
    @endif

    <x-en-tete-page :titre="auth()->user()->estChef() ? 'Ma filière : '.$filiere->code : $filiere->code" :sous-titre="$filiere->libelle">
        @can('create', App\Models\ProcesVerbal::class)
            <a class="bouton bouton--secondaire" href="{{ route('pv.scanner') }}">Scanner un PV</a>
            <a class="bouton" href="{{ route('pv.create') }}">+ Ajouter un PV</a>
        @endcan
    </x-en-tete-page>

    <x-barre-recherche :exemple="'Ex. : '.$filiere->code.' 2023 S3'" />

    <section class="section" aria-labelledby="titre-promotions">
        <h2 id="titre-promotions" class="section__titre">Promotions</h2>

        @if ($promotions->isEmpty())
            <x-etat-vide titre="Aucune promotion dans cette filière">
                @if (auth()->user()->estAdmin())
                    <a class="bouton" href="{{ route('admin.promotions.create', ['filiere' => $filiere->id]) }}">+ Ajouter une promotion</a>
                @else
                    La direction doit d'abord créer les promotions de votre filière.
                @endif
            </x-etat-vide>
        @else
            <ul class="grille" role="list">
                @foreach ($promotions as $promotion)
                    <li>
                        <a class="carte carte--lien" href="{{ route('consultation.annees', $promotion) }}">
                            <span class="carte__titre">{{ $promotion->libelle }}</span>
                            <span class="carte__meta">{{ $promotion->proces_verbaux_count }} PV archivé(s)</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
@endsection
