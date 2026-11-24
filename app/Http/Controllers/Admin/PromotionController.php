<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PromotionRequest;
use App\Models\Filiere;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * UC04 : l'admin gère les promotions de chaque filière.
 * Une promotion qui a des PV ne peut pas être supprimée (RG11).
 */
class PromotionController extends Controller
{
    public function index(Request $request): View
    {
        $promotions = Promotion::with('filiere')
            ->withCount('procesVerbaux')
            ->when($request->integer('filiere'), fn ($q, $id) => $q->where('filiere_id', $id))
            ->orderBy(Filiere::select('code')->whereColumn('filieres.id', 'promotions.filiere_id'))
            ->orderByDesc('libelle')
            ->paginate(25)
            ->withQueryString();

        return view('admin.promotions.index', [
            'promotions' => $promotions,
            'filieres' => Filiere::parCode()->get(),
            'filiereFiltre' => $request->integer('filiere') ?: null,
        ]);
    }

    public function create(Request $request): View
    {
        // ?filiere=3 pré-sélectionne la filière (lien depuis la liste des filières).
        $promotion = new Promotion(['filiere_id' => $request->integer('filiere') ?: null]);

        return view('admin.promotions.formulaire', ['promotion' => $promotion, 'filieres' => Filiere::parCode()->get()]);
    }

    public function store(PromotionRequest $request): RedirectResponse
    {
        Promotion::create($request->validated());

        return redirect()->route('admin.promotions.index')->with('succes', 'La promotion a été ajoutée.');
    }

    public function edit(Promotion $promotion): View
    {
        return view('admin.promotions.formulaire', ['promotion' => $promotion, 'filieres' => Filiere::parCode()->get()]);
    }

    public function update(PromotionRequest $request, Promotion $promotion): RedirectResponse
    {
        $promotion->update($request->validated());

        return redirect()->route('admin.promotions.index')->with('succes', 'La promotion a été modifiée.');
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        if ($promotion->procesVerbaux()->exists()) {
            return back()->with('erreur', 'Impossible : cette promotion contient des PV (RG11).');
        }

        $promotion->delete();

        return redirect()->route('admin.promotions.index')->with('succes', 'La promotion a été supprimée.');
    }
}
