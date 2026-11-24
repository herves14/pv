{{--
|--------------------------------------------------------------------------
| Fil d'Ariane : « Filières › GRH › Promotion 2022-2025 › 2023-2024 »
|--------------------------------------------------------------------------
| Paramètre : $etapes = [ ['libelle' => '...', 'url' => '...'], ... ]
| La dernière étape (page en cours) n'a pas de lien.
| Exemple : @include('partials.fil-ariane', ['etapes' => [...]])
--}}
<nav class="fil-ariane" aria-label="Fil d'Ariane">
    <ol class="fil-ariane__liste">
        @foreach ($etapes as $etape)
            <li class="fil-ariane__etape">
                @if (! $loop->last && ! empty($etape['url']))
                    <a href="{{ $etape['url'] }}">{{ $etape['libelle'] }}</a>
                @else
                    <span aria-current="page">{{ $etape['libelle'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
