<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Promotion = une cohorte d'étudiants d'une filière (ex. « Promotion 2022-2025»).
 * Une promotion appartient à une seule filière (RG08).
 */
class Promotion extends Model
{
    use HasFactory;

    protected $fillable = ['filiere_id', 'libelle'];

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }

    public function procesVerbaux(): HasMany
    {
        return $this->hasMany(ProcesVerbal::class);
    }
}
