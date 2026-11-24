<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Année académique, ex. « 2023-2024 » (RG09).
 * Le nom de table est précisé car Laravel ne sait pas mettre
 * « AnneeAcademique » au pluriel français.
 */
class AnneeAcademique extends Model
{
    use HasFactory;

    protected $table = 'annees_academiques';

    protected $fillable = ['libelle'];

    public function procesVerbaux(): HasMany
    {
        return $this->hasMany(ProcesVerbal::class);
    }
}
