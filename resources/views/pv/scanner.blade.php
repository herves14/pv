{{--
|--------------------------------------------------------------------------
| Scanner un PV avec la caméra (UC10) — maquette écran 9
|--------------------------------------------------------------------------
| Fonctionnement (public/js/scanner.js) :
|  1. l'utilisateur photographie chaque page :
|       - téléphone / tablette : « Prendre une photo » ouvre l'appareil photo ;
|       - ordinateur : « Utiliser la webcam » affiche la caméra dans la page ;
|  2. les pages s'affichent en miniatures (réordonner, supprimer) ;
|  3. à l'envoi, les photos sont réduites (1 700 px) et assemblées en UN PDF
|     dans le navigateur (bibliothèque jsPDF, public/js/vendor) ;
|  4. le PDF est placé dans le champ « fichier » et le formulaire est envoyé
|     au même traitement que l'ajout classique (ProcesVerbalController@store).
| Un scanner de bureau ne peut pas être piloté par un navigateur : dans ce
| cas, on numérise vers un PDF puis on utilise « Ajouter un PV ».
--}}
@extends('layouts.app')

@section('titre', 'Scanner un PV')

@section('contenu')
    <x-en-tete-page titre="Scanner un PV" sous-titre="Photographiez chaque page, l'application en fait un seul PDF.">
        <a class="bouton bouton--secondaire" href="{{ route('pv.create') }}">J'ai déjà un PDF</a>
    </x-en-tete-page>

    <noscript>
        <div class="alerte alerte--erreur">Le scan nécessite JavaScript. Utilisez « J'ai déjà un PDF » pour téléverser un fichier.</div>
    </noscript>

    <form method="POST" action="{{ route('pv.store') }}" enctype="multipart/form-data" class="formulaire" data-scanner
          data-taille-max="{{ App\Http\Requests\Pv\StoreProcesVerbalRequest::TAILLE_MAX_KO * 1024 }}">
        @csrf

        {{-- 1. Les pages --}}
        <section class="scan" aria-labelledby="titre-pages">
            <h2 id="titre-pages" class="section__titre">1. Pages du PV</h2>

            <div class="scan__boutons">
                {{-- capture="environment" : ouvre directement la caméra arrière sur téléphone --}}
                <label class="bouton">
                    Prendre une photo
                    <input class="visuellement-cache" type="file" accept="image/*" capture="environment" multiple data-scan-photos>
                </label>
                <button type="button" class="bouton bouton--secondaire" data-scan-webcam>Utiliser la webcam</button>
                <label class="bouton bouton--secondaire">
                    Importer des images
                    <input class="visuellement-cache" type="file" accept="image/*" multiple data-scan-photos>
                </label>
            </div>

            {{-- Aperçu de la webcam (ordinateur), caché tant qu'on ne l'a pas demandée --}}
            <div class="scan__camera" data-scan-camera hidden>
                <video class="scan__video" data-scan-video autoplay playsinline muted></video>
                <div class="scan__boutons">
                    <button type="button" class="bouton" data-scan-capturer>Capturer cette page</button>
                    <button type="button" class="bouton bouton--secondaire" data-scan-fermer>Fermer la webcam</button>
                </div>
            </div>

            <p class="scan__statut" data-scan-statut aria-live="polite">Aucune page pour le moment.</p>
            <ol class="scan__pages" data-scan-pages></ol>

            @error('fichier')<p class="champ__erreur">{{ $message }}</p>@enderror

            {{-- Champ rempli par scanner.js avec le PDF généré --}}
            <input class="visuellement-cache" type="file" name="fichier" accept="application/pdf" data-scan-fichier tabindex="-1" aria-hidden="true">
        </section>

        {{-- 2. Le classement --}}
        <section class="formulaire" aria-labelledby="titre-classement">
            <h2 id="titre-classement" class="section__titre">2. Classement</h2>
            @include('pv._champs-classement')
        </section>

        <div class="formulaire__actions">
            <button type="submit" class="bouton" data-scan-envoyer disabled>Créer le PDF et archiver</button>
            <a href="{{ route('accueil') }}" class="bouton bouton--secondaire">Annuler</a>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('js/vendor/jspdf.umd.min.js') }}" defer></script>
    <script src="{{ asset('js/scanner.js') }}?v={{ @filemtime(public_path('js/scanner.js')) }}" defer></script>
@endpush
