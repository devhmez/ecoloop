# EcoLoop

Projet Technologies Web 2A12 — ESPRIT 2026/2027 · Groupe **VERTEX**

Application web en PHP 8 (architecture MVC, PDO, MySQL) sans framework.

## Modules

| Module | Responsable | État |
|---|---|---|
| Utilisateurs (inscription, connexion, profil, mot de passe oublié, back office CRUD) | Hamza Dellai | fait |

## Lancer le projet

1. Copier le dossier dans `htdocs` (XAMPP ou MAMP).
2. Démarrer Apache et MySQL.
3. phpMyAdmin → Importer → `database/ecoloop.sql`.
4. Ouvrir `http://localhost/<dossier>/` (MAMP : port 8888, et `DB_PASS = 'root'` dans `config/database.php`).

Comptes de démonstration : `admin@ecoloop.tn` / `Admin123` (admin), `sami@ecoloop.tn` / `Citoyen123` (citoyen).

## Structure

```
index.php          contrôleur frontal : index.php?r=<page>
config/            connexion PDO
helpers/           fonctions communes (e(), csrf, flash, redirect…)
models/            requêtes SQL (une classe par table)
controllers/       logique de chaque page
views/             HTML (layout/ = en-tête et pied de page communs)
assets/            CSS et JavaScript (contrôle de saisie sans HTML5)
database/          script SQL de la base commune
```

Pour ajouter un module : un modèle dans `models/`, un contrôleur dans `controllers/`, ses vues dans `views/<module>/`, ses routes dans le tableau `$routes` de `index.php`, et ses tables dans `database/ecoloop.sql`.
