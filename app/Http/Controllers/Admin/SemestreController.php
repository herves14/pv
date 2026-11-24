<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SemestreRequest;
use App\Models\Semestre;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** UC04 : l'admin gère les semestres (S1 à S6 par défaut, RG10). */
class SemestreController extends Controller
{
    public function index(): View
    {
        $semestres = Semestre::withCount('procesVerbaux')->ordonnes()->get();

        return view('admin.semestres.index', compact('semestres'));
    }

    public function create(): View
    {
        return view('admin.semestres.formulaire', ['semestre' => new Semestre(['ordre' => Semestre::max('ordre') + 1])]);
    }

    public function store(SemestreRequest $request): RedirectResponse
    {
        Semestre::create($request->validated());

        return redirect()->route('admin.semestres.index')->with('succes', 'Le semestre a été ajouté.');
    }

    public function edit(Semestre $semestre): View
    {
        return view('admin.semestres.formulaire', compact('semestre'));
    }

    public function update(SemestreRequest $request, Semestre $semestre): RedirectResponse
    {
        $semestre->update($request->validated());

        return redirect()->route('admin.semestres.index')->with('succes', 'Le semestre a été modifié.');
    }

    public function destroy(Semestre $semestre): RedirectResponse
    {
        if ($semestre->procesVerbaux()->exists()) {
            return back()->with('erreur', 'Impossible : des PV sont classés dans ce semestre (RG11).');
        }

        $semestre->delete();

        return redirect()->route('admin.semestres.index')->with('succes', 'Le semestre a été supprimé.');
    }
}
