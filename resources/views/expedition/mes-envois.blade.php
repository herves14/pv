{{--
|--------------------------------------------------------------------------
| Espace du service d'expédition : « Mes envois » (RG20)
|--------------------------------------------------------------------------
| L'agent ne voit que les PV qu'il a lui-même téléversés ou scannés.
| Contrôleur : MesEnvoisController. $envois est paginé (15 par page).
--}}
@extends('layouts.app')

@section('titre', 'Mes envois')

@section('contenu')
    <x-en-tete-page titre="Mes envois" sous-titre="Les PV que vous avez numérisés, du plus récent au plus ancien.">
        <a class="bouton bouton--secondaire" href="{{ route('pv.scanner') }}">Scanner un PV</a>
        <a class="bouton" href="{{ route('pv.create') }}">+ Téléverser un PV</a>
    </x-en-tete-page>

    <x-barre-recherche exemple="Ex. : STAT 2022 S5" />

    @if ($envois->isEmpty())
        <x-etat-vide titre="Vous n'avez encore envoyé aucun PV">
            Numérisez un PV papier puis téléversez le PDF, ou scannez-le avec la caméra.
        </x-etat-vide>
    @else
        <p class="section__meta">{{ $envois->total() }} PV envoyé(s)</p>
        <div class="liste-pv">
            @foreach ($envois as $pv)
                <x-carte-pv :pv="$pv" />
            @endforeach
        </div>

        {{ $envois->links('partials.pagination') }}
    @endif
@endsection
