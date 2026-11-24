{{--
|--------------------------------------------------------------------------
| Consultation, étape 3 : semestres et PV d'une année — maquette écran 3
|--------------------------------------------------------------------------
| Un bloc par semestre (S1 à S6), avec ses PV (session normale, rattrapage).
| « Ouvrir le PDF » ouvre le fichier dans le navigateur (UC08).
| Contrôleur : ConsultationController@semestres.
| $pvParSemestre : PV regroupés par semestre_id.
--}}
@extends('layouts.app')

@section('titre', $promotion->libelle.' · '.$annee->libelle)

@section('contenu')
    @include('partials.fil-ariane', ['etapes' => array_values(array_filter([
        auth()->user()->estAdmin() ? ['libelle' => 'Filières', 'url' => route('consultation.filieres')] : null,
        ['libelle' => $filiere->code, 'url' => auth()->user()->estChef() ? route('departement.index') : route('consultation.promotions', $filiere)],
        ['libelle' => $promotion->libelle, 'url' => route('consultation.annees', $promotion)],
        ['libelle' => $annee->libelle],
    ]))])

    <x-en-tete-page :titre="'Année '.$annee->libelle" :sous-titre="$filiere->code.' · '.$promotion->libelle">
        @can('televerserDans', [App\Models\ProcesVerbal::class, $promotion])
            <a class="bouton" href="{{ route('pv.create', ['promotion' => $promotion->id, 'annee' => $annee->id]) }}">+ Ajouter un PV</a>
        @endcan
    </x-en-tete-page>

    <div class="semestres">
        @foreach ($semestres as $semestre)
            @php($pvs = $pvParSemestre->get($semestre->id, collect()))

            <section class="semestre" aria-labelledby="semestre-{{ $semestre->id }}">
                <h2 id="semestre-{{ $semestre->id }}" class="semestre__titre">
                    {{ $semestre->libelle }}
                    <span class="semestre__compte">{{ $pvs->count() }} PV</span>
                </h2>

                @forelse ($pvs as $pv)
                    <x-carte-pv :pv="$pv" :classement="false" />
                @empty
                    <p class="semestre__vide">Aucun PV pour ce semestre.</p>
                @endforelse
            </section>
        @endforeach
    </div>
@endsection
