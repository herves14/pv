<?php

namespace App\Http\Requests\Pv;

use App\Http\Requests\RequeteFormulaire;

/**
 * Formulaire « Modifier un PV » (UC06) : nouveau fichier facultatif,
 * date et observation. Le classement (promotion, année, semestre, session)
 * ne change pas : pour reclasser, on supprime puis on ajoute à nouveau.
 */
class UpdateProcesVerbalRequest extends RequeteFormulaire
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('pv'));
    }

    public function rules(): array
    {
        return [
            'date_deliberation' => ['nullable', 'date', 'before_or_equal:today'],
            'fichier' => ['nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:'.StoreProcesVerbalRequest::TAILLE_MAX_KO],
            'observation' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'date_deliberation' => 'date de délibération',
            'fichier' => 'fichier PDF',
            'observation' => 'observation',
        ];
    }
}
