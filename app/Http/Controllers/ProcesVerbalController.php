<?php

namespace App\Http\Controllers;

use App\Enums\SessionPv;
use App\Http\Requests\Pv\StoreProcesVerbalRequest;
use App\Http\Requests\Pv\UpdateProcesVerbalRequest;
use App\Models\AnneeAcademique;
use App\Models\Filiere;
use App\Models\ProcesVerbal;
use App\Models\Semestre;
use App\Models\User;
use App\Services\GestionPvService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Ajouter (UC05), scanner (UC10), modifier et supprimer (UC06) un PV.
 *
 * Ouvert aux trois rôles ; les droits fins sont dans ProcesVerbalPolicy :
 *  - admin et expédition ajoutent dans toutes les filières ;
 *  - le chef n'ajoute que dans SA filière (RG22) : le formulaire ne lui
 *    propose qu'elle, ET StoreProcesVerbalRequest refuse toute autre promotion.
 * L'enregistrement des fichiers est délégué à GestionPvService.
 */
class ProcesVerbalController extends Controller
{
    public function __construct(private readonly GestionPvService $service) {}

    /** Formulaire « Ajouter un PV » (PDF déjà numérisé). */
    public function create(Request $request): View
    {
        Gate::authorize('create', ProcesVerbal::class);

        return view('pv.creer', $this->donneesFormulaire($request));
    }

    /** Écran « Scanner un PV » : photos avec la caméra, assemblées en PDF dans le navigateur. */
    public function scanner(Request $request): View
    {
        Gate::authorize('create', ProcesVerbal::class);

        return view('pv.scanner', $this->donneesFormulaire($request));
    }

    /** Enregistre le PV (même traitement pour l'ajout et pour le scan). */
    public function store(StoreProcesVerbalRequest $request): RedirectResponse
    {
        $pv = $this->service->creer($request->validated(), $request->file('fichier'), $request->user());

        return redirect($this->pageApresEnvoi($request->user(), $pv))
            ->with('succes', "Le {$pv->titre} a bien été archivé.");
    }

    /** Formulaire « Modifier / remplacer le fichier ». */
    public function edit(ProcesVerbal $pv): View
    {
        Gate::authorize('update', $pv);

        $pv->load(['promotion.filiere', 'anneeAcademique', 'semestre']);

        return view('pv.modifier', compact('pv'));
    }

    public function update(UpdateProcesVerbalRequest $request, ProcesVerbal $pv): RedirectResponse
    {
        $this->service->modifier($pv, $request->validated(), $request->file('fichier'));

        return redirect($this->pageApresEnvoi($request->user(), $pv))
            ->with('succes', 'Le PV a été mis à jour.');
    }

    /** Supprime le PV et son fichier (RG17). */
    public function destroy(Request $request, ProcesVerbal $pv): RedirectResponse
    {
        Gate::authorize('delete', $pv);

        $page = $this->pageApresEnvoi($request->user(), $pv);
        $this->service->supprimer($pv);

        return redirect($page)->with('succes', 'Le PV a été supprimé.');
    }

    // ------------------------------------------------------------ Outils internes

    /**
     * Listes du formulaire, déjà filtrées selon le rôle :
     * un chef ne reçoit que sa filière, donc ne peut pas en choisir une autre.
     */
    private function donneesFormulaire(Request $request): array
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Collection<int, Filiere> $filieres */
        $filieres = Filiere::parCode()
            ->when($user->estChef(), fn ($q) => $q->whereKey($user->filiere_id))
            ->with(['promotions' => fn ($q) => $q->orderByDesc('libelle')])
            ->get();

        return [
            'filieres' => $filieres,
            'filiereVerrouillee' => $user->estChef() ? $filieres->first() : null,
            'annees' => AnneeAcademique::orderByDesc('libelle')->get(),
            'semestres' => Semestre::ordonnes()->get(),
            'sessions' => SessionPv::options(),
            // Pré-remplissage quand on arrive depuis une page de promotion : ?promotion=12
            'promotionChoisie' => $request->integer('promotion') ?: null,
            'anneeChoisie' => $request->integer('annee') ?: null,
        ];
    }

    /**
     * Où revenir après un ajout, une modification ou une suppression :
     * l'expédition retrouve la liste de ses envois, les autres la page des semestres.
     */
    private function pageApresEnvoi(User $user, ProcesVerbal $pv): string
    {
        if ($user->estExpedition()) {
            return route('expedition.index');
        }

        return route('consultation.semestres', [$pv->promotion_id, $pv->annee_academique_id]);
    }
}
