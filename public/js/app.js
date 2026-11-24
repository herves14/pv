/* ==========================================================================
   Scripts communs à toutes les pages connectées
   ==========================================================================
   Chargé par resources/views/partials/scripts.blade.php (attribut defer).
   Sans bibliothèque. Chaque fonctionnalité est une fonction indépendante,
   activée seulement si les éléments concernés existent sur la page
   (repérés par des attributs data-*).

     1. initialiserMenu()             menu hamburger         [data-bouton-menu]
     2. initialiserRecherche()        recherche instantanée  [data-recherche]
     3. initialiserFiltrePromotions() filière -> promotions  [data-filtre-promotions]
     4. initialiserChampRole()        champ filière du chef  [data-role]
     5. initialiserConfirmations()    « Êtes-vous sûr ? »    [data-confirmer]
     6. initialiserEnvoiAuto()        filtres sans bouton    [data-envoi-auto]
     7. initialiserControleTaille()   PDF de plus de 20 Mo   [data-taille-max]

   Important : tout ceci améliore le confort, mais la sécurité est faite
   côté serveur (middlewares, Policies, validation). Sans JavaScript,
   l'application reste utilisable.
   ========================================================================== */
(function () {
    'use strict';

    /* 1. Menu hamburger ---------------------------------------------------
       Ouvre / ferme le panneau. Se ferme aussi avec Échap et par un clic
       en dehors. aria-expanded tient les lecteurs d'écran informés. */
    function initialiserMenu() {
        var bouton = document.querySelector('[data-bouton-menu]');
        var menu = document.querySelector('[data-menu]');
        if (!bouton || !menu) return;

        function ouvrir() {
            menu.hidden = false;
            bouton.setAttribute('aria-expanded', 'true');
            bouton.setAttribute('aria-label', 'Fermer le menu');
            var premierLien = menu.querySelector('a, button');
            if (premierLien) premierLien.focus();
        }

        function fermer(rendreFocus) {
            menu.hidden = true;
            bouton.setAttribute('aria-expanded', 'false');
            bouton.setAttribute('aria-label', 'Ouvrir le menu');
            if (rendreFocus) bouton.focus();
        }

        bouton.addEventListener('click', function () {
            menu.hidden ? ouvrir() : fermer(false);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !menu.hidden) fermer(true);
        });

        document.addEventListener('click', function (e) {
            if (!menu.hidden && !menu.contains(e.target) && !bouton.contains(e.target)) fermer(false);
        });
    }

    /* 2. Recherche instantanée ---------------------------------------------
       300 ms après la dernière frappe, demande au serveur le fragment HTML
       des résultats (/recherche?q=...&partiel=1) et l'affiche sous la barre.
       AbortController annule la requête précédente si on tape encore : on
       n'affiche jamais d'anciens résultats par-dessus des récents. */
    function initialiserRecherche() {
        document.querySelectorAll('[data-recherche]').forEach(function (formulaire) {
            var champ = formulaire.querySelector('input[name="q"]');
            var zone = formulaire.querySelector('[data-resultats]');
            // Sur la page /recherche, les résultats initiaux sont remplacés par la zone dynamique.
            var initiaux = document.querySelector('[data-resultats-initiaux]');
            var minuteur = null;
            var requete = null;

            function chercher() {
                var texte = champ.value.trim();
                if (requete) requete.abort();

                if (texte.length === 0) {
                    zone.innerHTML = '';
                    if (initiaux) initiaux.hidden = true;
                    return;
                }

                requete = new AbortController();
                formulaire.setAttribute('aria-busy', 'true');

                var url = formulaire.action + '?partiel=1&q=' + encodeURIComponent(texte);
                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                    credentials: 'same-origin',
                    signal: requete.signal
                })
                    .then(function (reponse) {
                        // Session expirée : on recharge pour revenir à la connexion.
                        if (reponse.status === 401 || reponse.redirected) { window.location.reload(); return null; }
                        if (!reponse.ok) throw new Error('HTTP ' + reponse.status);
                        return reponse.text();
                    })
                    .then(function (html) {
                        if (html === null) return;
                        zone.innerHTML = html;
                        if (initiaux) initiaux.hidden = true;
                        initialiserConfirmations(zone); // les boutons « Supprimer » ajoutés
                        // L'adresse suit la recherche : on peut la copier ou revenir en arrière.
                        if (initiaux) history.replaceState(null, '', formulaire.action + '?q=' + encodeURIComponent(texte));
                    })
                    .catch(function (erreur) {
                        if (erreur.name === 'AbortError') return;
                        zone.innerHTML = '<p class="recherche__message">La recherche a échoué. Vérifiez votre connexion et réessayez.</p>';
                    })
                    .finally(function () { formulaire.removeAttribute('aria-busy'); });
            }

            champ.addEventListener('input', function () {
                clearTimeout(minuteur);
                minuteur = setTimeout(chercher, 300);
            });

            // Entrée : résultat immédiat sans quitter la page.
            formulaire.addEventListener('submit', function (e) {
                e.preventDefault();
                clearTimeout(minuteur);
                chercher();
            });
        });
    }

    /* 3. Filière -> promotions ---------------------------------------------
       La liste des promotions contient un <optgroup> par filière. Quand on
       choisit une filière, on ne garde que son groupe. On reconstruit la
       liste (plutôt que de masquer des options) : c'est fiable sur iPhone. */
    function initialiserFiltrePromotions() {
        document.querySelectorAll('[data-filtre-promotions]').forEach(function (choixFiliere) {
            var liste = document.getElementById(choixFiliere.getAttribute('data-filtre-promotions'));
            if (!liste) return;

            var groupes = Array.prototype.slice.call(liste.querySelectorAll('optgroup')).map(function (g) {
                return g.cloneNode(true);
            });

            function appliquer() {
                var filiere = choixFiliere.value;
                var choisie = liste.value;
                liste.querySelectorAll('optgroup').forEach(function (g) { g.remove(); });

                groupes.forEach(function (groupe) {
                    if (filiere === '' || groupe.getAttribute('data-filiere') === filiere) {
                        liste.appendChild(groupe.cloneNode(true));
                    }
                });

                // On garde la promotion choisie si elle est encore dans la liste.
                liste.value = choisie;
                if (liste.value !== choisie) liste.value = '';
            }

            choixFiliere.addEventListener('change', appliquer);
            appliquer();
        });
    }

    /* 4. Champ « Filière » du formulaire de compte --------------------------
       Affiché seulement si le rôle choisi est « chef de département » (RG03). */
    function initialiserChampRole() {
        var role = document.querySelector('[data-role]');
        if (!role) return;

        function appliquer() {
            document.querySelectorAll('[data-si-role]').forEach(function (bloc) {
                var visible = bloc.getAttribute('data-si-role') === role.value;
                bloc.hidden = !visible;
                bloc.querySelectorAll('select, input').forEach(function (champ) {
                    champ.disabled = !visible; // un champ désactivé n'est pas envoyé
                    champ.required = visible;
                });
            });
        }

        role.addEventListener('change', appliquer);
        appliquer();
    }

    /* 5. Confirmation avant une action irréversible (suppression, désactivation) */
    function initialiserConfirmations(racine) {
        (racine || document).querySelectorAll('form[data-confirmer]').forEach(function (formulaire) {
            if (formulaire.dataset.confirmationPrete) return; // déjà branché
            formulaire.dataset.confirmationPrete = '1';
            formulaire.addEventListener('submit', function (e) {
                if (!window.confirm(formulaire.getAttribute('data-confirmer'))) e.preventDefault();
            });
        });
    }

    /* 6. Listes de filtre envoyées dès qu'on change la valeur */
    function initialiserEnvoiAuto() {
        document.querySelectorAll('[data-envoi-auto]').forEach(function (liste) {
            liste.addEventListener('change', function () { liste.form.submit(); });
        });
    }

    /* 7. Contrôle de la taille du PDF avant l'envoi (le serveur revérifie) */
    function initialiserControleTaille() {
        document.querySelectorAll('input[type="file"][data-taille-max]').forEach(function (champ) {
            champ.addEventListener('change', function () {
                var max = parseInt(champ.getAttribute('data-taille-max'), 10);
                var fichier = champ.files[0];
                if (fichier && fichier.size > max) {
                    champ.setCustomValidity('Ce fichier dépasse ' + Math.round(max / 1048576) + ' Mo.');
                } else {
                    champ.setCustomValidity('');
                }
                champ.reportValidity();
            });
        });

        // Évite le double envoi d'un gros fichier (double clic).
        document.querySelectorAll('[data-envoi]').forEach(function (bouton) {
            bouton.form.addEventListener('submit', function () {
                setTimeout(function () {
                    bouton.disabled = true;
                    bouton.textContent = 'Envoi en cours…';
                }, 0);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initialiserMenu();
        initialiserRecherche();
        initialiserFiltrePromotions();
        initialiserChampRole();
        initialiserConfirmations();
        initialiserEnvoiAuto();
        initialiserControleTaille();
    });
})();
