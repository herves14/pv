<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use App\Http\Requests\RequeteFormulaire;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

/**
 * Création et modification d'un compte par l'admin (UC03).
 *
 * Règles contrôlées :
 *  - RG01 : un rôle parmi les trois ;
 *  - RG03 : filière obligatoire pour un chef, interdite pour les autres ;
 *  - RG04 : une filière a au plus un chef actif ;
 *  - RG06 : l'admin ne peut pas retirer son propre rôle d'administrateur ;
 *  - mot de passe obligatoire à la création, facultatif en modification.
 */
class UtilisateurRequest extends RequeteFormulaire
{
    public function authorize(): bool
    {
        return $this->user()->estAdmin();
    }

    public function rules(): array
    {
        /** @var User|null $cible compte modifié (null à la création) */
        $cible = $this->route('utilisateur');

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($cible)],
            'role' => ['required', Rule::enum(Role::class)],
            'filiere_id' => [
                'nullable',
                'required_if:role,'.Role::ChefDepartement->value,
                'prohibited_unless:role,'.Role::ChefDepartement->value,
                'integer',
                'exists:filieres,id',
            ],
            'password' => [$cible ? 'nullable' : 'required', 'confirmed', Password::min(8)],
        ];
    }

    /** RG06 et RG04, vérifiées après les règles simples. */
    public function after(): array
    {
        return [
            // RG06 : sinon l'application pourrait se retrouver sans aucun admin.
            function (Validator $validator) {
                $cible = $this->route('utilisateur');
                if ($cible !== null && $cible->is($this->user()) && $this->input('role') !== Role::Admin->value) {
                    $validator->errors()->add('role', 'Vous ne pouvez pas retirer votre propre rôle d\'administrateur.');
                }
            },
            // RG04 : un seul chef actif par filière.
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty() || $this->input('role') !== Role::ChefDepartement->value) {
                    return;
                }

                $cible = $this->route('utilisateur');
                // Un compte désactivé qu'on modifie ne compte pas comme « actif ».
                if ($cible !== null && ! $cible->actif) {
                    return;
                }

                $dejaUnChef = User::actifs()
                    ->where('role', Role::ChefDepartement->value)
                    ->where('filiere_id', $this->integer('filiere_id'))
                    ->when($cible, fn ($q) => $q->whereKeyNot($cible->id))
                    ->exists();

                if ($dejaUnChef) {
                    $validator->errors()->add('filiere_id', 'Cette filière a déjà un chef actif. Désactivez d\'abord son compte (RG04).');
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nom complet',
            'email' => 'adresse e-mail',
            'role' => 'rôle',
            'filiere_id' => 'filière',
            'password' => 'mot de passe',
        ];
    }

    public function messages(): array
    {
        return [
            'filiere_id.required_if' => 'Choisissez la filière dirigée par ce chef de département.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ] + parent::messages();
    }
}
