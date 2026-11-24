<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Compte utilisateur (table users).
 *
 * Colonnes ajoutées à la table Laravel d'origine :
 *  - role       : admin | chef_departement | expedition (RG01)
 *  - filiere_id : filière dirigée, renseignée seulement pour un chef (RG03)
 *  - actif      : false = le compte ne peut plus se connecter (RG05)
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** Colonnes remplissables en masse (User::create([...])). */
    protected $fillable = ['name', 'email', 'password', 'role', 'filiere_id', 'actif'];

    /** Colonnes jamais renvoyées en JSON. */
    protected $hidden = ['password', 'remember_token'];

    /** Conversions automatiques des colonnes. */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // le mot de passe est chiffré automatiquement
            'role' => Role::class,
            'actif' => 'boolean',
        ];
    }

    // ------------------------------------------------------------ Relations

    /** Filière dirigée (chef de département uniquement). */
    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }

    /** PV téléversés par cet utilisateur (RG16). */
    public function pvTeleverses(): HasMany
    {
        return $this->hasMany(ProcesVerbal::class, 'televerse_par');
    }

    // --------------------------------------------------------------- Scopes

    /** User::actifs() : comptes autorisés à se connecter. */
    public function scopeActifs(Builder $query): Builder
    {
        return $query->where('actif', true);
    }

    // ------------------------------------------------------ Tests de rôle

    public function estAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function estChef(): bool
    {
        return $this->role === Role::ChefDepartement;
    }

    public function estExpedition(): bool
    {
        return $this->role === Role::Expedition;
    }

    /** Vrai si l'utilisateur est le chef de cette filière (RG19, RG22). */
    public function dirige(Filiere|int|null $filiere): bool
    {
        $id = $filiere instanceof Filiere ? $filiere->id : $filiere;

        return $this->estChef() && $this->filiere_id !== null && $this->filiere_id === $id;
    }
}
