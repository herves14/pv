<?php

namespace App\Enums;

/**
 * Les trois rôles de l'application (RG01).
 * Un compte possède exactement un rôle.
 */
enum Role: string
{
    case Admin = 'admin';
    case ChefDepartement = 'chef_departement';
    case Expedition = 'expedition';

    public function libelle(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur (direction)',
            self::ChefDepartement => 'Chef de département',
            self::Expedition => "Service d'expédition",
        };
    }

    /** Nom de la route d'accueil vers laquelle on redirige après connexion (RG21). */
    public function routeAccueil(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::ChefDepartement => 'departement.index',
            self::Expedition => 'expedition.index',
        };
    }
}
