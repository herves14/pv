<?php

namespace App\Models;

use App\Enums\SessionPv;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Procès-verbal de délibération : un PDF scanné + ses informations de classement.
 *
 * Classement (RG12, RG13) : promotion (donc filière) + année + semestre + session.
 * Il existe au plus un PV par combinaison (index unique en base).
 * La filière n'est PAS stockée ici : on la retrouve par $pv->promotion->filiere.
 * Le fichier est sur le disque privé « local » (RG14) ; voir GestionPvService.
 */
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
            'fichier_taille' => 'integer',
        ];
    }

    // ------------------------------------------------------------ Relations

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

    /** Utilisateur qui a téléversé le PV (RG16). */
    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'televerse_par');
    }

    // ---------------------------------------------------- Attributs calculés

    /** « PV S3 · Session normale » */
    public function getTitreAttribute(): string
    {
        return 'PV '.$this->semestre->code.' · '.$this->session->libelle();
    }

    /** « 3,2 Mo » ou « 850 Ko » */
    public function getTailleLisibleAttribute(): string
    {
        $octets = (int) $this->fichier_taille;

        return $octets >= 1048576
            ? number_format($octets / 1048576, 1, ',', ' ').' Mo'
            : max(1, (int) round($octets / 1024)).' Ko';
    }

    // --------------------------------------------------------------- Scopes

    /**
     * Ne garde que les PV que l'utilisateur a le droit de voir.
     * À utiliser dans TOUTES les listes (RG18 admin, RG19 chef, RG20 expédition).
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

    /**
     * Recherche « intelligente » (UC11) : chaque mot tapé doit apparaître dans
     * la promotion, la filière, l'année, le semestre ou la session.
     * Toujours APRÈS visiblesPar() (RG25) :
     *   ProcesVerbal::visiblesPar($user)->recherche('GRH 2023 S3')->get();
     */
    public function scopeRecherche(Builder $query, ?string $texte): Builder
    {
        $mots = preg_split('/\s+/', trim((string) $texte), -1, PREG_SPLIT_NO_EMPTY);

        // Au plus 6 mots : au-delà, la requête devient lourde pour un gain nul.
        foreach (array_slice($mots, 0, 6) as $mot) {
            $like = '%'.$mot.'%';

            $query->where(function (Builder $q) use ($like) {
                $q->where('session', 'like', $like)
                    ->orWhereHas('promotion', fn (Builder $p) => $p->where('libelle', 'like', $like)
                        ->orWhereHas('filiere', fn (Builder $f) => $f->where('code', 'like', $like)
                            ->orWhere('libelle', 'like', $like)))
                    ->orWhereHas('anneeAcademique', fn (Builder $a) => $a->where('libelle', 'like', $like))
                    ->orWhereHas('semestre', fn (Builder $s) => $s->where('code', 'like', $like)
                        ->orWhere('libelle', 'like', $like));
            });
        }

        return $query;
    }

    /** Charge en une fois tout ce qu'il faut pour afficher un PV (évite les requêtes N+1). */
    public function scopeAvecClassement(Builder $query): Builder
    {
        return $query->with(['promotion.filiere', 'anneeAcademique', 'semestre', 'auteur']);
    }
}
