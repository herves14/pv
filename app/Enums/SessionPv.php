<?php

namespace App\Enums;

/**
 * Session de délibération d'un PV (RG13) : normale ou rattrapage.
 * Stockée dans proces_verbaux.session, convertie en SessionPv par le modèle.
 */
enum SessionPv: string
{
    case Normale = 'normale';
    case Rattrapage = 'rattrapage';

    public function libelle(): string
    {
        return match ($this) {
            self::Normale => 'Session normale',
            self::Rattrapage => 'Session de rattrapage',
        };
    }

    /** Liste [valeur => libellé] pour les formulaires. */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $session) {
            $options[$session->value] = $session->libelle();
        }

        return $options;
    }
}
