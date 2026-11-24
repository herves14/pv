<?php

namespace App\Enums;

/** Session de délibération d'un PV (RG13). */
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
}
