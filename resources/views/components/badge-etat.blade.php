{{--
| Composant <x-badge-etat :actif="$utilisateur->actif" /> : pastille « Actif » / « Désactivé ».
--}}
@props(['actif'])

<span @class(['badge', 'badge--actif' => $actif, 'badge--inactif' => ! $actif])>
    {{ $actif ? 'Actif' : 'Désactivé' }}
</span>
