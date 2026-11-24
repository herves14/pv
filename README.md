# Archives des PV de délibération · ENEAM

Application Laravel minimaliste pour **consulter les PV de délibération scannés (PDF)**,
rangés par filière, promotion, année académique et semestre.

## Les trois rôles

| Rôle | Ce qu'il peut faire |
| --- | --- |
| `admin` (directeur) | Tout : crée les comptes, ajoute des filières, gère les référentiels, consulte tout |
| `chef_departement` | Consulte et ajoute les PV de sa filière uniquement |
| `expedition` | Téléverse les PV PDF numérisés (et revoit ses propres envois) |

Pas de saisie de notes, pas de génération de PV, pas de recherche par matricule (évolution possible plus tard).

## Base de données (5 tables métier)

`filieres`, `promotions`, `annees_academiques`, `semestres`, `proces_verbaux`, plus `users`
(colonnes `role`, `filiere_id`, `actif`). Les règles de gestion RGxx citées dans le code
renvoient au document de modélisation.

## Installation

```sh
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # ou configurer MySQL dans .env
php artisan migrate --seed       # crée les 8 filières, S1 à S6 et le compte admin
php artisan serve
```

Connexion : identifiants `ADMIN_EMAIL` / `ADMIN_PASSWORD` du fichier `.env`.

Les PDF sont stockés dans `storage/app/private` (disque `local`), jamais dans `public/` :
chaque ouverture passe par `FichierPvController`, qui vérifie les droits avec `ProcesVerbalPolicy`.

Le scan avec la caméra fonctionne sur téléphone partout ; la webcam d'un ordinateur
exige que le site soit servi en **HTTPS** (ou sur `localhost`).

## Où trouver quoi

Le plan détaillé, fichier par fichier, est dans [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).
Chaque fichier commence par un commentaire qui explique son rôle.

## Tests

```sh
php artisan test
```

68 tests dans `tests/Feature` :

| Fichier | Ce qu'il vérifie |
| --- | --- |
| `MiddlewareEtRolesTest` | chaque espace n'accepte que ses rôles, compte désactivé déconnecté, 5 tentatives de connexion par minute, menu selon le rôle |
| `AccesPvTest` | redirection par rôle, cloisonnement des PV par filière, droits des Policies |
| `GestionPvTest` | ajout, scan, doublon refusé, PDF seulement, 20 Mo maximum, remplacement et suppression du fichier, chef limité à sa filière |
| `AdministrationTest` | comptes (RG03, RG04, RG06), filières (RG11, RG23), format des années, toutes les pages admin |
| `RechercheTest` | la recherche ne montre que les PV autorisés (RG25) |
