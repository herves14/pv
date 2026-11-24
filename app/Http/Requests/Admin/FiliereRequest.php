<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\RequeteFormulaire;
use Illuminate\Validation\Rule;

/** Ajout / modification d'une filière (RG07, RG23). */
class FiliereRequest extends RequeteFormulaire
{
    public function authorize(): bool
    {
        return $this->user()->estAdmin();
    }

    /** Le code est mis en majuscules avant validation : « grh » devient « GRH ». */
    protected function prepareForValidation(): void
    {
        $this->merge(['code' => mb_strtoupper(trim((string) $this->input('code')))]);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9-]+$/', Rule::unique('filieres', 'code')->ignore($this->route('filiere'))],
            'libelle' => ['required', 'string', 'max:150'],
        ];
    }

    public function attributes(): array
    {
        return ['code' => 'code', 'libelle' => 'nom complet'];
    }

    public function messages(): array
    {
        return ['code.regex' => 'Le code ne contient que des lettres, des chiffres et des tirets (ex. GRH).'] + parent::messages();
    }
}
