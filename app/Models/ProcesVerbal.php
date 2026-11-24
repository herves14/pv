<?php

namespace App\Models;

use App\Enums\SessionPv;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcesVerbal extends Model
{
    use HasFactory;

    protected $table = 'proces_verbaux';

    protected $fillable = [
        'promotion_id', 'annee_academique_id', 'semestre_id', 'session',
        'date_deliberation', 'fichier_chemin', 'fichier_nom_original',
        'fichier_taille', 'observation', 'televerse_par',
    ];

    protected function casts(): array
    {
        return [
            'session' => SessionPv::class,
            'date_deliberation' => 'date',
        ];
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function anneeAcademique(): BelongsTo
    {
        return $this->belongsTo(AnneeAcademique::class);
    }

    public function semestre(): BelongsTo
    {
        return $this->belongsTo(Semestre::class);
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'televerse_par');
    }

    /**
     * Ne garde que les PV qu'un utilisateur a le droit de voir
     * (RG18, RG19, RG20). À utiliser dans toutes les listes.
     */
    public function scopeVisiblesPar(Builder $query, User $user): Builder
    {
        return match (true) {
            $user->estAdmin() => $query,
            $user->estChef() => $query->whereHas(
                'promotion',
                fn (Builder $q) => $q->where('filiere_id', $user->filiere_id)
            ),
            default => $query->where('televerse_par', $user->id),
        };
    }
}
