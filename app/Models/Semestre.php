<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Semestre S1 à S6 (RG10). « ordre » fixe l'ordre d'affichage des onglets.
 */
class Semestre extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'libelle', 'ordre'];

    public function procesVerbaux(): HasMany
    {
        return $this->hasMany(ProcesVerbal::class);
    }

    /** Semestre::ordonnes() : S1, S2, S3… */
    public function scopeOrdonnes(Builder $query): Builder
    {
        return $query->orderBy('ordre');
    }
}
