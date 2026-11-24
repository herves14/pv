<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'filiere_id', 'actif'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'actif' => 'boolean',
        ];
    }

    /** Filière dirigée (seulement pour un chef de département, RG03). */
    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }

    /** PV envoyés par cet utilisateur (RG16). */
    public function pvTeleverses(): HasMany
    {
        return $this->hasMany(ProcesVerbal::class, 'televerse_par');
    }

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
}
