<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titre', 'PV') · ENEAM</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; background: #f6f7f9; color: #1d2330; }
        header { background: #12355b; color: #fff; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; }
        main { max-width: 960px; margin: 24px auto; padding: 0 16px; }
        .carte { background: #fff; border-radius: 8px; padding: 24px; box-shadow: 0 1px 2px rgba(0,0,0,.08); }
        label { display: block; margin-top: 12px; font-weight: 600; }
        input[type=email], input[type=password] { width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box; }
        button { margin-top: 16px; padding: 8px 16px; background: #12355b; color: #fff; border: 0; border-radius: 6px; cursor: pointer; }
        .erreur { color: #b00020; margin-top: 8px; }
    </style>
</head>
<body>
<header>
    <strong>Archives des PV · ENEAM</strong>
    @auth
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <span>{{ auth()->user()->name }} ({{ auth()->user()->role->libelle() }})</span>
            <button type="submit" style="margin:0 0 0 12px">Se déconnecter</button>
        </form>
    @endauth
</header>
<main>
    @yield('contenu')
</main>
</body>
</html>
