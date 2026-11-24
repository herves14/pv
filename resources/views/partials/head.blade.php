{{--
| Contenu de la balise <head>, commun à toutes les pages.
| Le « ?v=... » ajouté au CSS change à chaque modification du fichier :
| le navigateur télécharge alors la nouvelle version au lieu de garder l'ancienne.
--}}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@hasSection('titre')@yield('titre') · @endif{{ config('app.name') }}</title>
<link rel="icon" href="{{ asset('favicon.ico') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}">
