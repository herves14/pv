{{--
|--------------------------------------------------------------------------
| Tableau de bord de l'administrateur — maquette écran 6
|--------------------------------------------------------------------------
| Contrôleur : Admin\TableauDeBordController.
| Variables : $nbPv, $nbFilieres, $nbComptesActifs, $filieres, $derniersPv.
--}}
@extends('layouts.app')

@section('titre', 'Tableau de bord')

@section('contenu')
    <x-en-tete-page titre="Tableau de bord" sous-titre="Vue d'ensemble des archives et des comptes.">
        <a class="bouton bouton--secondaire" href="{{ route('pv.scanner') }}">Scanner un PV</a>
        <a class="bouton" href="{{ route('pv.create') }}">+ Ajouter un PV</a>
    </x-en-tete-page>

    {{-- Chiffres clés --}}
    <ul class="grille grille--stats" role="list">
        <li class="carte stat">
            <span class="stat__libelle">PV archivés</span>
            <span class="stat__valeur">{{ $nbPv }}</span>
            <a class="stat__lien" href="{{ route('consultation.filieres') }}">Consulter</a>
        </li>
        <li class="carte stat">
            <span class="stat__libelle">Filières</span>
            <span class="stat__valeur">{{ $nbFilieres }}</span>
            <a class="stat__lien" href="{{ route('admin.filieres.create') }}">+ Ajouter une filière</a>
        </li>
        <li class="carte stat">
            <span class="stat__libelle">Comptes actifs</span>
            <span class="stat__valeur">{{ $nbComptesActifs }}</span>
            <a class="stat__lien" href="{{ route('admin.utilisateurs.create') }}">+ Nouveau compte</a>
        </li>
    </ul>

    {{-- État des filières : PV et chef de département --}}
    <section class="section" aria-labelledby="titre-filieres">
        <h2 id="titre-filieres" class="section__titre">Filières</h2>
        <div class="tableau">
            <table>
                <thead>
                    <tr><th scope="col">Filière</th><th scope="col">Chef de département</th><th scope="col">PV</th><th scope="col"><span class="visuellement-cache">Actions</span></th></tr>
                </thead>
                <tbody>
                    @foreach ($filieres as $filiere)
                        @php($chef = $filiere->chefs->firstWhere('actif', true))
                        <tr>
                            <td><strong>{{ $filiere->code }}</strong> <span class="texte-discret">{{ $filiere->libelle }}</span></td>
                            <td>
                                @if ($chef)
                                    {{ $chef->name }}
                                @else
                                    <span class="badge badge--attention">Aucun chef actif</span>
                                @endif
                            </td>
                            <td>{{ $filiere->proces_verbaux_count }}</td>
                            <td><a href="{{ route('consultation.promotions', $filiere) }}">Consulter</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- Derniers PV archivés --}}
    <section class="section" aria-labelledby="titre-derniers">
        <h2 id="titre-derniers" class="section__titre">Derniers PV archivés</h2>
        @if ($derniersPv->isEmpty())
            <x-etat-vide titre="Aucun PV archivé pour le moment" />
        @else
            <div class="liste-pv">
                @foreach ($derniersPv as $pv)
                    <x-carte-pv :pv="$pv" />
                @endforeach
            </div>
        @endif
    </section>
@endsection
