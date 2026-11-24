<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/** Une filière = un département de l'ENEAM. */
class Filiere extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'libelle'];

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    public function procesVerbaux(): HasManyThrough
    {
        return $this->hasManyThrough(ProcesVerbal::class, Promotion::class);
    }

    /** Comptes de chef rattachés (actifs ou non) ; RG04 : un seul actif. */
    public function chefs(): HasMany
    {
        return $this->hasMany(User::class)->where('role', Role::ChefDepartement->value);
    }
}
