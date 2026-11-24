<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\RequeteFormulaire;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/** Ajout / modification d'une année académique (RG09 : format AAAA-AAAA, unique). */
class AnneeAcademiqueRequest extends RequeteFormulaire
{
    public function authorize(): bool
    {
        return $this->user()->estAdmin();
    }

    public function rules(): array
    {
        return [
            'libelle' => ['required', 'regex:/^\d{4}-\d{4}$/', Rule::unique('annees_academiques', 'libelle')->ignore($this->route('annee'))],
        ];
    }

    /** La seconde année doit suivre la première : 2023-2024, pas 2023-2026. */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('libelle')) {
                    return;
                }
                [$debut, $fin] = array_map('intval', explode('-', $this->input('libelle')));
                if ($fin !== $debut + 1) {
                    $validator->errors()->add('libelle', 'La seconde année doit suivre la première (ex. 2023-2024).');
                }
            },
        ];
    }

    public function attributes(): array
    {
        return ['libelle' => 'année académique'];
    }

    public function messages(): array
    {
        return ['libelle.regex' => 'Écrivez l\'année au format 2023-2024.'] + parent::messages();
    }
}
