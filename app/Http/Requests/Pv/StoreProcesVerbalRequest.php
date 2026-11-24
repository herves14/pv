<?php

namespace App\Http\Requests\Pv;

use App\Enums\SessionPv;
use App\Http\Requests\RequeteFormulaire;
use App\Models\ProcesVerbal;
use App\Models\Promotion;
use Illuminate\Validation\Rule;

/**
 * Formulaire « Ajouter un PV » (UC05) et fin du scan (UC10).
 *
 * authorize() applique RG22 : un chef ne peut ajouter que dans une promotion
 * de sa filière, même s'il modifie le formulaire dans son navigateur.
 */
class StoreProcesVerbalRequest extends RequeteFormulaire
{
    /** Taille maximale d'un PDF, en Ko (RG14 : 20 Mo). */
    public const TAILLE_MAX_KO = 20480;

    public function authorize(): bool
    {
        $promotion = Promotion::find($this->integer('promotion_id'));

        // Promotion absente ou inconnue : on laisse la validation afficher l'erreur.
        if ($promotion === null) {
            return $this->user()->can('create', ProcesVerbal::class);
        }

        return $this->user()->can('televerserDans', [ProcesVerbal::class, $promotion]);
    }

    public function rules(): array
    {
        return [
            'promotion_id' => ['required', 'integer', 'exists:promotions,id'],
            'annee_academique_id' => ['required', 'integer', 'exists:annees_academiques,id'],
            'semestre_id' => ['required', 'integer', 'exists:semestres,id'],
            'session' => [
                'required',
                Rule::enum(SessionPv::class),
                // RG13 : au plus un PV par (promotion, année, semestre, session)
                Rule::unique('proces_verbaux')->where(fn ($q) => $q
                    ->where('promotion_id', $this->input('promotion_id'))
                    ->where('annee_academique_id', $this->input('annee_academique_id'))
                    ->where('semestre_id', $this->input('semestre_id'))),
            ],
            'date_deliberation' => ['nullable', 'date', 'before_or_equal:today'],
            'fichier' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:'.self::TAILLE_MAX_KO],
            'observation' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'session.unique' => 'Un PV existe déjà pour cette promotion, cette année, ce semestre et cette session. Remplacez son fichier au lieu d\'en ajouter un autre.',
        ] + parent::messages();
    }

    public function attributes(): array
    {
        return [
            'promotion_id' => 'promotion',
            'annee_academique_id' => 'année académique',
            'semestre_id' => 'semestre',
            'session' => 'session',
            'date_deliberation' => 'date de délibération',
            'fichier' => 'fichier PDF',
            'observation' => 'observation',
        ];
    }
}
