{{--
|--------------------------------------------------------------------------
| Fichiers JavaScript
|--------------------------------------------------------------------------
| app.js : menu hamburger, recherche instantanée, listes liées, confirmations.
| Une page peut en ajouter d'autres avec @push('scripts') ... @endpush
| (ex. l'écran de scan charge jsPDF et scanner.js).
| « defer » : le script s'exécute une fois la page entièrement lue.
--}}
<script src="{{ asset('js/app.js') }}?v={{ @filemtime(public_path('js/app.js')) }}" defer></script>
@stack('scripts')
