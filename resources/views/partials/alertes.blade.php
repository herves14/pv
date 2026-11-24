{{--
|--------------------------------------------------------------------------
| Messages affichés après une action
|--------------------------------------------------------------------------
| - session('succes') : action réussie (ex. « Le PV a bien été archivé. »)
| - session('erreur') : action refusée (ex. règle RG04, RG11)
| - $errors           : erreurs de formulaire ; le détail s'affiche aussi
|                       sous chaque champ (composant x-champ).
| role="status" / role="alert" : lus automatiquement par les lecteurs d'écran.
--}}
@if (session('succes'))
    <div class="alerte alerte--succes" role="status">{{ session('succes') }}</div>
@endif

@if (session('erreur'))
    <div class="alerte alerte--erreur" role="alert">{{ session('erreur') }}</div>
@endif

@if ($errors->any())
    <div class="alerte alerte--erreur" role="alert">
        @if ($errors->count() === 1)
            {{ $errors->first() }}
        @else
            <p class="alerte__titre">Le formulaire contient {{ $errors->count() }} erreurs :</p>
            <ul class="alerte__liste">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        @endif
    </div>
@endif
