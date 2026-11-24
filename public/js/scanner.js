/* ==========================================================================
   Scan d'un PV avec la caméra (UC10)
   ==========================================================================
   Utilisé uniquement par resources/views/pv/scanner.blade.php, après
   public/js/vendor/jspdf.umd.min.js (bibliothèque jsPDF, licence MIT).

   Étapes :
     1. Récupérer les pages :
          - photo prise par l'appareil photo du téléphone / de la tablette
            (<input type="file" accept="image/*" capture="environment">) ;
          - image importée depuis l'ordinateur ;
          - capture depuis la webcam (getUserMedia, HTTPS obligatoire).
     2. Réduire chaque image à 1 700 px de côté maximum (RG24) : lisible à
        l'écran et à l'impression, mais un PDF bien plus léger.
     3. À l'envoi : assembler les pages en UN PDF (une image par page A4),
        le placer dans le champ « fichier » et envoyer le formulaire normal.
        Le serveur le traite exactement comme un PDF téléversé.
   ========================================================================== */
(function () {
    'use strict';

    var COTE_MAX = 1700;        // pixels, plus grand côté d'une page (RG24)
    var QUALITE_JPEG = 0.82;    // compromis lisibilité / poids
    var MAX_PAGES = 60;

    document.addEventListener('DOMContentLoaded', function () {
        var formulaire = document.querySelector('[data-scanner]');
        if (!formulaire) return;

        var elements = {
            photos: formulaire.querySelectorAll('[data-scan-photos]'),
            webcam: formulaire.querySelector('[data-scan-webcam]'),
            camera: formulaire.querySelector('[data-scan-camera]'),
            video: formulaire.querySelector('[data-scan-video]'),
            capturer: formulaire.querySelector('[data-scan-capturer]'),
            fermer: formulaire.querySelector('[data-scan-fermer]'),
            statut: formulaire.querySelector('[data-scan-statut]'),
            liste: formulaire.querySelector('[data-scan-pages]'),
            fichier: formulaire.querySelector('[data-scan-fichier]'),
            envoyer: formulaire.querySelector('[data-scan-envoyer]')
        };
        var tailleMax = parseInt(formulaire.getAttribute('data-taille-max'), 10);

        /** Pages déjà réduites : [{ url: 'data:image/jpeg...', largeur, hauteur }] */
        var pages = [];
        var flux = null; // flux vidéo de la webcam

        // ------------------------------------------------------------ Affichage

        function afficherStatut(texte, estErreur) {
            elements.statut.textContent = texte;
            elements.statut.classList.toggle('scan__statut--erreur', !!estErreur);
        }

        /** Redessine les miniatures avec leurs boutons ↑ ↓ et Retirer. */
        function afficherPages() {
            elements.liste.innerHTML = '';

            pages.forEach(function (page, index) {
                var item = document.createElement('li');
                item.className = 'scan__page';
                item.innerHTML =
                    '<img class="scan__miniature" alt="Page ' + (index + 1) + '">' +
                    '<span class="scan__numero">Page ' + (index + 1) + '</span>' +
                    '<div class="scan__outils">' +
                    '  <button type="button" class="scan__outil" data-action="monter" aria-label="Avancer la page ' + (index + 1) + '">↑</button>' +
                    '  <button type="button" class="scan__outil" data-action="descendre" aria-label="Reculer la page ' + (index + 1) + '">↓</button>' +
                    '  <button type="button" class="scan__outil scan__outil--supprimer" data-action="retirer" aria-label="Retirer la page ' + (index + 1) + '">Retirer</button>' +
                    '</div>';
                item.querySelector('img').src = page.url;
                item.querySelector('[data-action="monter"]').disabled = index === 0;
                item.querySelector('[data-action="descendre"]').disabled = index === pages.length - 1;
                item.dataset.index = index;
                elements.liste.appendChild(item);
            });

            elements.envoyer.disabled = pages.length === 0;
            afficherStatut(pages.length === 0
                ? 'Aucune page pour le moment.'
                : pages.length + ' page(s) prête(s). Ajoutez-en d\'autres ou archivez le PV.');
        }

        // Un seul écouteur pour tous les boutons des miniatures (délégation).
        elements.liste.addEventListener('click', function (e) {
            var bouton = e.target.closest('[data-action]');
            if (!bouton) return;
            var i = parseInt(bouton.closest('.scan__page').dataset.index, 10);
            var action = bouton.getAttribute('data-action');

            if (action === 'retirer') pages.splice(i, 1);
            if (action === 'monter' && i > 0) pages.splice(i - 1, 0, pages.splice(i, 1)[0]);
            if (action === 'descendre' && i < pages.length - 1) pages.splice(i + 1, 0, pages.splice(i, 1)[0]);
            afficherPages();
        });

        // ------------------------------------------------------- Traitement image

        /** Dessine une source (image ou vidéo) dans un canvas réduit, renvoie la page. */
        function reduire(source, largeur, hauteur) {
            var echelle = Math.min(1, COTE_MAX / Math.max(largeur, hauteur));
            var canvas = document.createElement('canvas');
            canvas.width = Math.round(largeur * echelle);
            canvas.height = Math.round(hauteur * echelle);
            var ctx = canvas.getContext('2d');
            ctx.fillStyle = '#FFFFFF'; // fond blanc (images PNG transparentes)
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(source, 0, 0, canvas.width, canvas.height);

            return { url: canvas.toDataURL('image/jpeg', QUALITE_JPEG), largeur: canvas.width, hauteur: canvas.height };
        }

        /** Lit un fichier image choisi / photographié et l'ajoute aux pages. */
        function ajouterFichierImage(fichier) {
            return new Promise(function (resoudre) {
                if (!fichier.type.startsWith('image/')) { resoudre(); return; }
                var url = URL.createObjectURL(fichier);
                var image = new Image();
                image.onload = function () {
                    pages.push(reduire(image, image.naturalWidth, image.naturalHeight));
                    URL.revokeObjectURL(url);
                    resoudre();
                };
                image.onerror = function () {
                    URL.revokeObjectURL(url);
                    afficherStatut('Une image n\'a pas pu être lue (format non pris en charge).', true);
                    resoudre();
                };
                image.src = url;
            });
        }

        elements.photos.forEach(function (champ) {
            champ.addEventListener('change', function () {
                var fichiers = Array.prototype.slice.call(champ.files, 0, MAX_PAGES - pages.length);
                afficherStatut('Préparation des pages…');
                fichiers.reduce(function (suite, f) {
                    return suite.then(function () { return ajouterFichierImage(f); });
                }, Promise.resolve()).then(function () {
                    champ.value = ''; // permet de reprendre la même photo
                    afficherPages();
                });
            });
        });

        // ------------------------------------------------------------- Webcam

        function arreterWebcam() {
            if (flux) flux.getTracks().forEach(function (piste) { piste.stop(); });
            flux = null;
            elements.camera.hidden = true;
        }

        elements.webcam.addEventListener('click', function () {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                afficherStatut('La caméra n\'est pas disponible ici (le site doit être en HTTPS). Utilisez « Prendre une photo » ou « Importer des images ».', true);
                return;
            }
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1080 } }, audio: false })
                .then(function (f) {
                    flux = f;
                    elements.video.srcObject = f;
                    elements.camera.hidden = false;
                    elements.capturer.focus();
                })
                .catch(function () {
                    afficherStatut('Accès à la caméra refusé ou aucune caméra trouvée.', true);
                });
        });

        elements.capturer.addEventListener('click', function () {
            var v = elements.video;
            if (!v.videoWidth || pages.length >= MAX_PAGES) return;
            pages.push(reduire(v, v.videoWidth, v.videoHeight));
            afficherPages();
        });

        elements.fermer.addEventListener('click', arreterWebcam);
        window.addEventListener('pagehide', arreterWebcam);

        // --------------------------------------------- Assemblage PDF et envoi

        /** Crée le PDF : une page A4 par image, orientée comme la photo. */
        function creerPdf() {
            var jsPDF = window.jspdf.jsPDF;
            var pdf = null;

            pages.forEach(function (page) {
                var paysage = page.largeur > page.hauteur;
                var orientation = paysage ? 'landscape' : 'portrait';
                if (pdf === null) {
                    pdf = new jsPDF({ orientation: orientation, unit: 'mm', format: 'a4', compress: true });
                } else {
                    pdf.addPage('a4', orientation);
                }

                // L'image est centrée et agrandie au maximum sans être déformée.
                var lp = pdf.internal.pageSize.getWidth();
                var hp = pdf.internal.pageSize.getHeight();
                var echelle = Math.min(lp / page.largeur, hp / page.hauteur);
                var l = page.largeur * echelle;
                var h = page.hauteur * echelle;
                pdf.addImage(page.url, 'JPEG', (lp - l) / 2, (hp - h) / 2, l, h);
            });

            return pdf.output('blob');
        }

        formulaire.addEventListener('submit', function (e) {
            e.preventDefault();

            if (pages.length === 0) { afficherStatut('Ajoutez au moins une page.', true); return; }
            if (!window.jspdf || typeof DataTransfer === 'undefined') {
                afficherStatut('Ce navigateur ne permet pas de créer le PDF. Mettez-le à jour ou utilisez « J\'ai déjà un PDF ».', true);
                return;
            }

            elements.envoyer.disabled = true;
            afficherStatut('Création du PDF…');

            // setTimeout : laisse le navigateur afficher le message avant le calcul.
            setTimeout(function () {
                try {
                    var blob = creerPdf();
                    if (blob.size > tailleMax) {
                        afficherStatut('Le PDF fait ' + (blob.size / 1048576).toFixed(1) + ' Mo, au-delà de la limite de 20 Mo. Retirez des pages.', true);
                        elements.envoyer.disabled = false;
                        return;
                    }

                    // On place le PDF dans le vrai champ <input type="file" name="fichier">.
                    var nom = 'scan-' + new Date().toISOString().slice(0, 10) + '.pdf';
                    var transfert = new DataTransfer();
                    transfert.items.add(new File([blob], nom, { type: 'application/pdf' }));
                    elements.fichier.files = transfert.files;

                    arreterWebcam();
                    afficherStatut('Envoi du PDF (' + (blob.size / 1048576).toFixed(1) + ' Mo)…');
                    // submit() envoie sans redéclencher cet écouteur ; les champs
                    // obligatoires ont déjà été vérifiés par le navigateur.
                    formulaire.submit();
                } catch (erreur) {
                    afficherStatut('La création du PDF a échoué. Réessayez avec moins de pages.', true);
                    elements.envoyer.disabled = false;
                }
            }, 30);
        });

        afficherPages();
    });
})();
