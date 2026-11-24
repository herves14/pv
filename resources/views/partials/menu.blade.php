{{--
|--------------------------------------------------------------------------
| Menu hamburger (panneau déroulant)
|--------------------------------------------------------------------------
| $liens est fourni par App\View\Composers\MenuComposer selon le rôle.
| Le panneau est caché par défaut (attribut hidden) ; public/js/app.js
| l'ouvre et le ferme (clic sur le bouton, touche Échap, clic à côté).
| Le lien de la page en cours est marqué aria-current="page" et surligné.
--}}
<nav id="menu-principal" class="menu" aria-label="Menu principal" data-menu hidden>
    <ul class="menu__liste">
        @foreach ($liens as $lien)
            <li>
                <a href="{{ $lien['url'] }}"
                   @class(['menu__lien', 'menu__lien--actif' => $lien['actif']])
                   @if ($lien['actif']) aria-current="page" @endif>
                    {{ $lien['libelle'] }}
                </a>
            </li>
        @endforeach
    </ul>

    <hr class="menu__separateur">

    {{-- La déconnexion se fait en POST (avec jeton CSRF), jamais par un simple lien --}}
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="menu__lien menu__lien--deconnexion">Se déconnecter</button>
    </form>
</nav>
