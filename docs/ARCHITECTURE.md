# Architecture du code

Ce document est la carte de l'application. Il dit où se trouve chaque chose et
pourquoi elle est là. Les numéros **RGxx** (règles de gestion) et **UCxx** (cas
d'utilisation) renvoient au document de modélisation.

## 1. Le trajet d'une requête

```
Navigateur
  -> routes/web.php                     quelle adresse, quel contrôleur
  -> middlewares                        auth, role:..., EnsureCompteActif
  -> Form Request (app/Http/Requests)   droits + validation des champs
  -> Contrôleur (app/Http/Controllers)  orchestre, reste court
  -> Policy (app/Policies)              droits sur CE PV / CETTE filière
  -> Service (app/Services)             logique métier (fichiers + base)
  -> Modèle (app/Models)                tables, relations, filtres
  -> Vue Blade (resources/views)        page HTML
```

## 2. La sécurité, en trois couches

| Couche | Fichier | Question à laquelle elle répond |
| --- | --- | --- |
| Middleware `auth` | `bootstrap/app.php` | Est-on connecté ? Sinon, page de connexion. |
| Middleware `EnsureCompteActif` | `app/Http/Middleware/EnsureCompteActif.php` | Le compte est-il toujours actif ? Sinon, déconnexion immédiate (RG05). |
| Middleware `role:...` | `app/Http/Middleware/EnsureRole.php` | Ce rôle a-t-il accès à cet **espace** ? (RG21) |
| Policies | `app/Policies/*.php` | Cet utilisateur a-t-il droit à **ce** PV, **cette** filière ? (RG17 à RG22) |
| Scope `visiblesPar()` | `app/Models/ProcesVerbal.php` | Quels PV mettre dans une **liste** ? (RG18 à RG20, RG25) |

Le menu ne montre que les liens autorisés, mais ce n'est **pas** une protection :
chaque adresse est vérifiée par les couches ci-dessus, même tapée à la main.

## 3. Back-end (dossier `app/`)

| Dossier / fichier | Rôle |
| --- | --- |
| `Enums/Role.php` | Les 3 rôles, leur libellé et leur page d'accueil |
| `Enums/SessionPv.php` | Session normale / rattrapage |
| `Models/` | `User`, `Filiere`, `Promotion`, `AnneeAcademique`, `Semestre`, `ProcesVerbal` |
| `Policies/ProcesVerbalPolicy.php` | voir, ajouter (`televerserDans`), modifier, supprimer un PV |
| `Policies/FilierePolicy.php` | voir une filière, la créer, la supprimer (RG11) |
| `Services/GestionPvService.php` | enregistre / remplace / supprime le PDF **et** la ligne en base, tout ou rien |
| `Http/Middleware/` | `EnsureRole`, `EnsureCompteActif` |
| `Http/Requests/RequeteFormulaire.php` | parent de tous les formulaires : messages d'erreur en français |
| `Http/Requests/Pv/` | ajout et modification d'un PV (PDF, 20 Mo, doublon RG13, filière RG22) |
| `Http/Requests/Admin/` | comptes (RG03, RG04, RG06), filières, promotions, années, semestres |
| `Http/Requests/Compte/` | changement de mot de passe |
| `Http/Controllers/Auth/ConnexionController.php` | connexion / déconnexion (UC01) |
| `Http/Controllers/AccueilController.php` | redirige chaque rôle vers son espace |
| `Http/Controllers/ConsultationController.php` | filières -> promotions -> années -> semestres (UC07) |
| `Http/Controllers/ProcesVerbalController.php` | ajouter, scanner, modifier, supprimer un PV (UC05, UC06, UC10) |
| `Http/Controllers/FichierPvController.php` | **seule** porte vers les PDF : ouvrir / télécharger (UC08) |
| `Http/Controllers/RechercheController.php` | recherche intelligente (UC11) |
| `Http/Controllers/MesEnvoisController.php` | espace du service d'expédition |
| `Http/Controllers/Compte/MotDePasseController.php` | changer son mot de passe (UC02) |
| `Http/Controllers/Admin/` | tableau de bord, utilisateurs, filières, promotions, années, semestres |
| `View/Composers/MenuComposer.php` | liste des liens du menu selon le rôle |

## 4. Front-end (dossiers `resources/views/` et `public/`)

Chaque morceau de la page est dans **son propre fichier** :

| Fichier | Contenu |
| --- | --- |
| `layouts/app.blade.php` | squelette des pages connectées (assemble les morceaux ci-dessous) |
| `layouts/invite.blade.php` | squelette de la page de connexion |
| `partials/head.blade.php` | balise `<head>` : titre, police, feuille de style |
| `partials/entete.blade.php` | **en-tête** : bouton hamburger, logo, nom et rôle |
| `partials/menu.blade.php` | panneau du **menu** hamburger |
| `partials/alertes.blade.php` | messages de succès et d'erreur |
| `partials/fil-ariane.blade.php` | fil d'Ariane (Filières › GRH › …) |
| `partials/pied-de-page.blade.php` | **pied de page** |
| `partials/scripts.blade.php` | fichiers JavaScript |
| `partials/pagination.blade.php` | Précédent / Suivant |
| `components/en-tete-page.blade.php` | titre de page + boutons d'action |
| `components/champ.blade.php` | libellé + champ + aide + erreur |
| `components/carte-pv.blade.php` | un PV dans une liste, avec ses actions autorisées |
| `components/barre-recherche.blade.php` | barre de recherche intelligente |
| `components/etat-vide.blade.php`, `badge-etat.blade.php` | liste vide, pastille Actif / Désactivé |
| `auth/`, `compte/`, `consultation/`, `pv/`, `recherche/`, `expedition/`, `admin/` | les pages, rangées par espace |
| `errors/` | pages 403, 404, 419, 429, 500 en français |
| `public/css/app.css` | toute la charte (blanc, bleu `#2563EB`, Inter), téléphone d'abord puis tablette (≥ 640 px) et ordinateur (≥ 1024 px) |
| `public/js/app.js` | menu hamburger, recherche instantanée, filière -> promotions, confirmations |
| `public/js/scanner.js` | scan : photos ou webcam, miniatures, assemblage en PDF |
| `public/js/vendor/jspdf.umd.min.js` | bibliothèque jsPDF 2.5.2 (licence MIT, voir `jspdf.LICENSE.txt`) |

## 5. Où stocker les fichiers PDF

Disque `local` = `storage/app/private`, rangés par filière : `pv/grh/<uuid>.pdf`.
Le nom aléatoire évite d'écraser un fichier ou de deviner une adresse. Le disque
n'est jamais servi directement (`'serve' => false` dans `config/filesystems.php`).

## 6. Ajouter une fonctionnalité : la marche à suivre

1. La route dans `routes/web.php`, dans le bon groupe de rôle.
2. Une Form Request si un formulaire est envoyé (droits + validation).
3. Une méthode de Policy si la décision dépend de l'objet (ce PV, cette filière).
4. Le contrôleur, court ; la logique métier lourde va dans un Service.
5. La vue, qui `@extends('layouts.app')` et réutilise les composants.
6. Un test dans `tests/Feature`, qui essaie aussi l'accès **interdit**.
