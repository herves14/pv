{{--
|--------------------------------------------------------------------------
| Composant <x-champ> : libellé + champ de saisie + aide + erreur
|--------------------------------------------------------------------------
| <x-champ nom="email" libelle="Adresse e-mail" aide="Texte facultatif">
|     <input id="email" name="email" type="email" ...>
| </x-champ>
| - « nom » doit être l'attribut name du champ : il sert à retrouver l'erreur
|   de validation correspondante et à relier le libellé (for="...").
| - « id-champ » : à donner seulement si l'id diffère du nom.
--}}
@props(['nom', 'libelle', 'aide' => null, 'idChamp' => null, 'facultatif' => false])

@php($id = $idChamp ?? $nom)

<div @class(['champ', 'champ--erreur' => $errors->has($nom)])>
    <label class="champ__libelle" for="{{ $id }}">
        {{ $libelle }}
        @if ($facultatif)<span class="champ__facultatif">(facultatif)</span>@endif
    </label>

    {{ $slot }}

    @if ($aide)
        <p class="champ__aide" id="{{ $id }}-aide">{{ $aide }}</p>
    @endif

    @error($nom)
        <p class="champ__erreur" id="{{ $id }}-erreur">{{ $message }}</p>
    @enderror
</div>
