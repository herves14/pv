<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FiliereRequest;
use App\Models\Filiere;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * UC04 / RG23 : l'admin ajoute, renomme et supprime les filières.
 * Suppression refusée si la filière a déjà des promotions (RG11, FilierePolicy).
 */
class FiliereController extends Controller
{
    public function index(): View
    {
        $filieres = Filiere::parCode()
            ->withCount(['promotions', 'procesVerbaux'])
            ->with('chefs')
            ->get();

        return view('admin.filieres.index', compact('filieres'));
    }

    public function create(): View
    {
        return view('admin.filieres.formulaire', ['filiere' => new Filiere]);
    }

    public function store(FiliereRequest $request): RedirectResponse
    {
        $filiere = Filiere::create($request->validated());

        return redirect()->route('admin.filieres.index')
            ->with('succes', "La filière {$filiere->code} a été ajoutée.");
    }

    public function edit(Filiere $filiere): View
    {
        return view('admin.filieres.formulaire', compact('filiere'));
    }

    public function update(FiliereRequest $request, Filiere $filiere): RedirectResponse
    {
        $filiere->update($request->validated());

        return redirect()->route('admin.filieres.index')
            ->with('succes', "La filière {$filiere->code} a été modifiée.");
    }

    public function destroy(Filiere $filiere): RedirectResponse
    {
        if (Gate::denies('delete', $filiere)) {
            return back()->with('erreur', 'Impossible : cette filière a déjà des promotions (RG11).');
        }

        $filiere->delete();

        return redirect()->route('admin.filieres.index')
            ->with('succes', "La filière {$filiere->code} a été supprimée.");
    }
}
