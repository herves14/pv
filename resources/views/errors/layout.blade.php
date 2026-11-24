{{--
|--------------------------------------------------------------------------
| Mise en page des pages d'erreur (403, 404, 419, 500)
|--------------------------------------------------------------------------
| Volontairement autonome : une page d'erreur peut s'afficher avant que la
| session soit chargée (ex. adresse inconnue). On n'y utilise donc ni le menu
| ni l'utilisateur connecté.
--}}
<!doctype html>
<html lang="fr">
<head>
    @include('partials.head')
</head>
<body class="corps-invite">
    <main id="contenu" class="page-invite">
        <div class="carte carte--erreur">
            <p class="erreur__code">@yield('code')</p>
            <h1 class="erreur__titre">@yield('titre')</h1>
            <p class="erreur__texte">@yield('message')</p>
            <a class="bouton" href="{{ url('/') }}">Retour à l'accueil</a>
        </div>
    </main>
</body>
</html>
