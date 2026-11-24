<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
