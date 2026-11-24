<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UtilisateurRequest;
use App\Models\Filiere;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * UC03 : l'admin gère les comptes (création, modification, activation).
 *
 * On ne supprime jamais un compte : on le désactive (RG05), pour garder
 * la trace de qui a téléversé chaque PV.
 * Règles : RG04 (un chef actif par filière), RG06 (l'admin ne peut ni se
 * désactiver ni retirer son propre rôle d'admin).
 */
class UtilisateurController extends Controller
{
    public function index(Request $request): View
    {
        $utilisateurs = User::with('filiere')
            ->when($request->query('role'), fn ($q, $role) => $q->where('role', $role))
            ->orderByDesc('actif')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.utilisateurs.index', [
            'utilisateurs' => $utilisateurs,
            'roles' => Role::options(),
            'roleFiltre' => $request->query('role'),
        ]);
    }

    public function create(): View
    {
        return view('admin.utilisateurs.formulaire', $this->listes() + ['utilisateur' => new User(['actif' => true])]);
    }

    public function store(UtilisateurRequest $request): RedirectResponse
    {
        $utilisateur = User::create($this->donnees($request) + ['actif' => true]);

        return redirect()->route('admin.utilisateurs.index')
            ->with('succes', "Le compte de {$utilisateur->name} a été créé.");
    }

    public function edit(User $utilisateur): View
    {
        return view('admin.utilisateurs.formulaire', $this->listes() + compact('utilisateur'));
    }

    public function update(UtilisateurRequest $request, User $utilisateur): RedirectResponse
    {
        $utilisateur->update($this->donnees($request));

        return redirect()->route('admin.utilisateurs.index')
            ->with('succes', "Le compte de {$utilisateur->name} a été modifié.");
    }

    /** Désactive un compte actif, ou réactive un compte désactivé. */
    public function basculerActivation(Request $request, User $utilisateur): RedirectResponse
    {
        // RG06 : l'admin ne peut pas se bloquer lui-même.
        if ($utilisateur->is($request->user())) {
            return back()->with('erreur', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        // RG04 : réactiver un chef n'est possible que si sa filière n'a pas déjà un chef actif.
        if (! $utilisateur->actif && $utilisateur->estChef()) {
            $occupee = User::actifs()
                ->where('role', Role::ChefDepartement->value)
                ->where('filiere_id', $utilisateur->filiere_id)
                ->exists();

            if ($occupee) {
                return back()->with('erreur', 'Cette filière a déjà un chef actif. Désactivez-le d\'abord (RG04).');
            }
        }

        $utilisateur->update(['actif' => ! $utilisateur->actif]);

        $etat = $utilisateur->actif ? 'réactivé' : 'désactivé';

        return back()->with('succes', "Le compte de {$utilisateur->name} a été {$etat}.");
    }

    // ------------------------------------------------------------ Outils internes

    /** Listes déroulantes du formulaire. */
    private function listes(): array
    {
        return [
            'roles' => Role::options(),
            'filieres' => Filiere::parCode()->get(),
        ];
    }

    /**
     * Données à enregistrer, à partir des champs validés.
     * - la filière n'est gardée que pour un chef (RG03) ;
     * - le mot de passe n'est changé que s'il a été saisi.
     */
    private function donnees(UtilisateurRequest $request): array
    {
        $donnees = $request->safe()->only(['name', 'email', 'role']);
        $donnees['filiere_id'] = $donnees['role'] === Role::ChefDepartement->value
            ? $request->integer('filiere_id')
            : null;

        if ($request->filled('password')) {
            $donnees['password'] = $request->validated('password'); // chiffré par le cast « hashed »
        }

        return $donnees;
    }
}
