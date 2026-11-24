<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Filière = un département de l'ENEAM (table filieres).
 * L'admin peut en ajouter à tout moment (RG23) ; le code est unique (RG07).
 */
class Filiere extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'libelle'];

    /** Promotions de la filière (RG08). */
    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    /** Tous les PV de la filière, en passant par ses promotions. */
    public function procesVerbaux(): HasManyThrough
    {
        return $this->hasManyThrough(ProcesVerbal::class, Promotion::class);
    }

    /** Comptes de chef rattachés, actifs ou non (RG04 : un seul actif). */
    public function chefs(): HasMany
    {
        return $this->hasMany(User::class)->where('role', Role::ChefDepartement->value);
    }

    /** Le chef actif de la filière, s'il existe. */
    public function chefActif(): ?User
    {
        return $this->chefs()->where('actif', true)->first();
    }

    /** Tri par défaut des listes : par code. */
    public function scopeParCode(Builder $query): Builder
    {
        return $query->orderBy('code');
    }

    /** « GRH · Gestion des Ressources Humaines » */
    public function getNomCompletAttribute(): string
    {
        return $this->code.' · '.$this->libelle;
    }
}
