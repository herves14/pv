<?php

namespace App\Http\Requests\Compte;

use App\Http\Requests\RequeteFormulaire;
use Illuminate\Validation\Rules\Password;

/** Changement de son propre mot de passe (UC02). */
class MotDePasseRequest extends RequeteFormulaire
{
    public function authorize(): bool
    {
        return true; // la route exige déjà d'être connecté
    }

    public function rules(): array
    {
        return [
            'mot_de_passe_actuel' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8), 'different:mot_de_passe_actuel'],
        ];
    }

    public function attributes(): array
    {
        return ['mot_de_passe_actuel' => 'mot de passe actuel', 'password' => 'nouveau mot de passe'];
    }

    public function messages(): array
    {
        return [
            'password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'password.different' => 'Le nouveau mot de passe doit être différent de l\'actuel.',
        ] + parent::messages();
    }
}
