# DataProd

Plateforme de suivi de production industrielle — gestion des opérateurs, saisie de résultats de tests, contrôle qualité et traçabilité produit.

> ⚠️ Projet de démonstration à but pédagogique et portfolio. Toutes les données, noms de produits et logiques métier sont **fictifs**. Aucune donnée réelle, aucun code issu d'un projet professionnel existant.

## Stack technique

- **Backend** : PHP 8.3, Symfony 7.4 (LTS)
- **Base de données** : MySQL / MariaDB, Doctrine ORM
- **Frontend** : Twig, Symfony Forms, Stimulus, Chart.js (à venir)
- **Tests** : PHPUnit
- **Environnement local** : XAMPP (MySQL), Symfony CLI

## Fonctionnalités prévues

- [x] Authentification (email/mot de passe) avec rôles hiérarchisés (Opérateur / Qualité / Admin)
- [ ] Gestion des utilisateurs (inscription réservée aux rôles Qualité/Admin)
- [ ] Intégration produit par lot (scan, sous-ensembles)
- [ ] Tests automatisés (import résultats banc de test, PASS/FAIL)
- [ ] Contrôle manuel/visuel
- [ ] Expédition (création box/palette)
- [ ] Dépannage (gestion des non-conformités)
- [ ] Monitoring (vue transverse par numéro de série)
- [ ] Statistiques de production
- [ ] API REST de consultation
- [ ] Notifications email automatiques

## Installation locale

### Prérequis

- PHP 8.2+
- Composer
- Symfony CLI
- MySQL / MariaDB

### Setup

\`\`\`bash
git clone https://github.com/ansenthandrayen/dataprod.git
cd dataprod
composer install
\`\`\`

Configurer la base de données dans \`.env\` :

\`\`\`
DATABASE_URL="mysql://root:@127.0.0.1:3306/suivi_production?serverVersion=10.4.32-MariaDB&charset=utf8mb4"
\`\`\`

Créer la base et lancer les migrations :

\`\`\`bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
\`\`\`

Charger les données fictives (utilisateurs de test) :

\`\`\`bash
php bin/console doctrine:fixtures:load
\`\`\`

Lancer le serveur local :

\`\`\`bash
symfony server:start
\`\`\`

L'application est accessible sur \`http://127.0.0.1:8000\`.

### Comptes de test

| Rôle      | Email                             | Mot de passe |
| --------- | --------------------------------- | ------------ |
| Admin     | admin@suivi-production.local      | password     |
| Qualité   | qualite@suivi-production.local    | password     |
| Opérateur | operateur1@suivi-production.local | password     |
| Opérateur | operateur2@suivi-production.local | password     |

## Architecture

Cycle de vie d'un produit dans l'application :

\`\`\`
Intégration (par lot) → Test (banc automatique) → Contrôle (manuel)
→ [si non conforme] → Dépannage → retest
→ [si conforme] → Expédition
\`\`\`

Le Monitoring offre une vue transverse de ce cycle par numéro de série.

## Auteur

Ansen — [github.com/ansenthandrayen](https://github.com/ansenthandrayen)
