<?php

namespace App\Enums;

/**
 * Les trois rôles de l'application (RG01).
 *
 * Un compte possède exactement UN rôle, stocké dans la colonne users.role.
 * Le modèle User convertit automatiquement cette colonne en Role (cast) :
 * on écrit donc $user->role === Role::Admin plutôt que de comparer des chaînes.
 */
enum Role: string
{
    case Admin = 'admin';
    case ChefDepartement = 'chef_departement';
    case Expedition = 'expedition';

    /** Nom affiché à l'écran. */
    public function libelle(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur (direction)',
            self::ChefDepartement => 'Chef de département',
            self::Expedition => "Service d'expédition",
        };
    }

    /** Route vers laquelle on redirige l'utilisateur après la connexion (RG21). */
    public function routeAccueil(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::ChefDepartement => 'departement.index',
            self::Expedition => 'expedition.index',
        };
    }

    /** Liste [valeur => libellé], pratique pour les listes déroulantes. */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $role) {
            $options[$role->value] = $role->libelle();
        }

        return $options;
    }
}
