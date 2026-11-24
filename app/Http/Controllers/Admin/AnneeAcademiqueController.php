<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnneeAcademiqueRequest;
use App\Models\AnneeAcademique;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** UC04 : l'admin gère les années académiques (format 2023-2024, RG09). */
class AnneeAcademiqueController extends Controller
{
    public function index(): View
    {
        $annees = AnneeAcademique::withCount('procesVerbaux')->orderByDesc('libelle')->get();

        return view('admin.annees.index', compact('annees'));
    }

    public function create(): View
    {
        return view('admin.annees.formulaire', ['annee' => new AnneeAcademique]);
    }

    public function store(AnneeAcademiqueRequest $request): RedirectResponse
    {
        AnneeAcademique::create($request->validated());

        return redirect()->route('admin.annees.index')->with('succes', 'L\'année académique a été ajoutée.');
    }

    public function edit(AnneeAcademique $annee): View
    {
        return view('admin.annees.formulaire', compact('annee'));
    }

    public function update(AnneeAcademiqueRequest $request, AnneeAcademique $annee): RedirectResponse
    {
        $annee->update($request->validated());

        return redirect()->route('admin.annees.index')->with('succes', 'L\'année académique a été modifiée.');
    }

    public function destroy(AnneeAcademique $annee): RedirectResponse
    {
        if ($annee->procesVerbaux()->exists()) {
            return back()->with('erreur', 'Impossible : des PV sont classés dans cette année (RG11).');
        }

        $annee->delete();

        return redirect()->route('admin.annees.index')->with('succes', 'L\'année académique a été supprimée.');
    }
}
