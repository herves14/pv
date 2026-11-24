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

## Tests

```sh
php artisan test
```

`tests/Feature/AccesPvTest.php` vérifie la redirection par rôle, le refus des comptes
désactivés et le cloisonnement des PV par filière.
