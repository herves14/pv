<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
