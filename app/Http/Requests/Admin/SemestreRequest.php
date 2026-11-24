<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\RequeteFormulaire;
use Illuminate\Validation\Rule;

/** Ajout / modification d'un semestre (RG10 : code unique, ordre d'affichage). */
class SemestreRequest extends RequeteFormulaire
{
    public function authorize(): bool
    {
        return $this->user()->estAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['code' => mb_strtoupper(trim((string) $this->input('code')))]);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'regex:/^S\d{1,2}$/', Rule::unique('semestres', 'code')->ignore($this->route('semestre'))],
            'libelle' => ['required', 'string', 'max:50'],
            'ordre' => ['required', 'integer', 'min:1', 'max:20'],
        ];
    }

    public function attributes(): array
    {
        return ['code' => 'code', 'libelle' => 'libellé', 'ordre' => 'ordre d\'affichage'];
    }

    public function messages(): array
    {
        return ['code.regex' => 'Le code s\'écrit S suivi d\'un numéro (ex. S3).'] + parent::messages();
    }
}
