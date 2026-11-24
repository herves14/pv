<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\RequeteFormulaire;
use Illuminate\Validation\Rule;

/** Ajout / modification d'une promotion (RG08 : libellé unique dans sa filière). */
class PromotionRequest extends RequeteFormulaire
{
    public function authorize(): bool
    {
        return $this->user()->estAdmin();
    }

    public function rules(): array
    {
        return [
            'filiere_id' => ['required', 'integer', 'exists:filieres,id'],
            'libelle' => [
                'required', 'string', 'max:100',
                Rule::unique('promotions', 'libelle')
                    ->where('filiere_id', $this->integer('filiere_id'))
                    ->ignore($this->route('promotion')),
            ],
        ];
    }

    public function attributes(): array
    {
        return ['filiere_id' => 'filière', 'libelle' => 'libellé'];
    }
}
