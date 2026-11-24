<?php

namespace App\Http\Controllers;

use App\Models\AnneeAcademique;
use App\Models\Filiere;
use App\Models\ProcesVerbal;
use App\Models\Promotion;
use App\Models\Semestre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * UC07 : parcourir les archives, de la filière jusqu'au PV.
 *
 *   filières  ->  promotions  ->  années  ->  semestres et PV  ->  PDF
 *
 * Sécurité, sur deux niveaux :
 *  1. la route exige le rôle admin ou chef_departement (middleware « role ») ;
 *  2. chaque méthode vérifie la filière demandée avec FilierePolicy::view :
 *     un chef qui tape l'adresse d'une autre filière reçoit une erreur 403.
 */
class ConsultationController extends Controller
{
    /** Étape 0 (admin) : toutes les filières, avec leur nombre de PV. */
    public function filieres(Request $request): View|RedirectResponse
    {
        // Un chef n'a qu'une filière : on lui épargne cette étape.
        if ($request->user()->estChef()) {
            return redirect()->route('departement.index');
        }

        $filieres = Filiere::parCode()
            ->withCount(['promotions', 'procesVerbaux'])
            ->get();

        return view('consultation.filieres', compact('filieres'));
    }

    /** Espace du chef (/departement) : la page de SA filière. */
    public function maFiliere(Request $request): View
    {
        $filiere = $request->user()->filiere;

        // Chef sans filière : incohérence à corriger par l'admin (RG03).
        abort_if($filiere === null, 403, 'Aucune filière n\'est associée à votre compte. Contactez la direction.');

        return $this->promotions($filiere);
    }

    /** Étape 1 : les promotions d'une filière. */
    public function promotions(Filiere $filiere): View
    {
        Gate::authorize('view', $filiere);

        $promotions = $filiere->promotions()
            ->withCount('procesVerbaux')
            ->orderByDesc('libelle')
            ->get();

        return view('consultation.promotions', compact('filiere', 'promotions'));
    }

    /** Étape 2 : les années académiques qui ont au moins un PV pour cette promotion. */
    public function annees(Promotion $promotion): View
    {
        Gate::authorize('view', $promotion->filiere);

        $annees = AnneeAcademique::query()
            ->whereHas('procesVerbaux', fn ($q) => $q->where('promotion_id', $promotion->id))
            ->withCount(['procesVerbaux' => fn ($q) => $q->where('promotion_id', $promotion->id)])
            ->orderByDesc('libelle')
            ->get();

        return view('consultation.annees', [
            'promotion' => $promotion,
            'filiere' => $promotion->filiere,
            'annees' => $annees,
        ]);
    }

    /** Étape 3 : tous les semestres de l'année, chacun avec ses PV (normale, rattrapage). */
    public function semestres(Promotion $promotion, AnneeAcademique $annee): View
    {
        Gate::authorize('view', $promotion->filiere);

        // Tous les PV de la combinaison, regroupés par semestre pour l'affichage.
        $pvParSemestre = ProcesVerbal::query()
            ->where('promotion_id', $promotion->id)
            ->where('annee_academique_id', $annee->id)
            ->avecClassement()
            ->orderBy('session')
            ->get()
            ->groupBy('semestre_id');

        return view('consultation.semestres', [
            'promotion' => $promotion,
            'filiere' => $promotion->filiere,
            'annee' => $annee,
            'semestres' => Semestre::ordonnes()->get(),
            'pvParSemestre' => $pvParSemestre,
        ]);
    }
}
