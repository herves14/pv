{{--
|--------------------------------------------------------------------------
| Mise en page des pages publiques (visiteur non connecté)
|--------------------------------------------------------------------------
| Utilisée par la page de connexion : pas de menu, contenu centré.
--}}
<!doctype html>
<html lang="fr">
<head>
    @include('partials.head')
</head>
<body class="corps-invite">
    <main id="contenu" class="page-invite">
        @yield('contenu')
    </main>

    @include('partials.pied-de-page')
</body>
</html>
