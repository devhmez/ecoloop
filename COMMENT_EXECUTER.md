# EcoLoop – Module « Récompenses & Échanges »

## Contenu du dossier
```
ecoloop/
├── login.php / login.html            connexion
├── index.php                         redirige vers le bon espace
├── frontoffice/   (côté citoyen)
│   ├── index.php / .html             accueil public
│   ├── recompenses.php / .html       solde, classement, succès, catalogue
│   ├── echanger.php / .html          confirmation de l'échange
│   ├── mes_echanges.php / .html      mes échanges + historique des points
│   └── annuler_echange.php           annulation (PHP seulement)
├── backoffice/    (côté admin)
│   ├── index.php / .html             tableau de bord
│   ├── recompenses.php / .html       gestion du catalogue
│   ├── recompense_form.php / .html   ajouter / modifier
│   └── echanges.php / .html          valider, remettre, annuler
├── includes/    (config, connexion BDD, fonctions, header, footer)
├── assets/      (css, js)
└── database/ecoloop.sql              base de données MySQL
```
Chaque page existe en deux versions : **.html** = maquette statique (données d'exemple), **.php** = version réelle connectée à la base.

## A. Voir la maquette HTML (sans XAMPP)
Double-clique sur `login.html` ou sur `frontoffice/index.html`.

## B. Lancer la version PHP + base de données (XAMPP)
1. Copie le dossier `ecoloop` dans `C:\xampp\htdocs\` (Mac : `/Applications/XAMPP/htdocs/`).
2. Ouvre XAMPP Control Panel, clique **Start** sur **Apache** et sur **MySQL** (ils doivent devenir verts).
3. Va sur `http://localhost/phpmyadmin` → onglet **Importer** → choisis `ecoloop/database/ecoloop.sql` → **Importer**.
4. Ouvre `http://localhost/ecoloop/` dans le navigateur.

| Espace | Adresse | Compte (mot de passe : `password`) |
|---|---|---|
| Front office | http://localhost/ecoloop/frontoffice/ | amira@ecoloop.tn |
| Back office  | http://localhost/ecoloop/backoffice/  | admin@ecoloop.tn |

## C. Scénario de test
1. Connecte-toi en citoyen (amira) → **Échanger** une récompense → un code `ECO-XXXXXX` apparaît dans « Mes échanges », les points baissent.
2. Déconnecte-toi, connecte-toi en admin → **Échanges** → **Valider**, puis **Marquer remis**.
3. Essaie **Annuler** sur un échange en attente : les points sont remboursés.
4. Dans le back office → **Récompenses** : ajoute, modifie, masque une récompense.

## D. Problèmes fréquents
- Apache ne démarre pas (port 80 pris) : XAMPP → Config → Service and Port Settings → port 8080, puis utilise `http://localhost:8080/ecoloop/`.
- « Connexion à la base impossible » : MySQL n'est pas vert, ou `ecoloop.sql` n'est pas importé.
- Page 404 : le dossier doit être `htdocs\ecoloop\` (pas un dossier dans un dossier).
- Pas de style : vérifie `BASE_URL` dans `includes/config.php` (`/ecoloop`).
- Les fichiers `.php` ne s'ouvrent pas par double-clic : passe toujours par `http://localhost/...`.

## E. Brancher le module sur le reste du projet
- Si tes collègues ont leur table des utilisateurs, ajoute-lui les colonnes `points` et `role`.
- Pour donner des points depuis un autre module :
```php
db()->prepare('UPDATE utilisateurs SET points = points + ? WHERE id = ?')->execute([10, $uid]);
db()->prepare("INSERT INTO historique_points (utilisateur_id, type, points, description) VALUES (?, 'gain', ?, ?)")
     ->execute([$uid, 10, 'Scan d\'un objet']);
```
- Le login est minimal : tout système marche tant que `$_SESSION['uid']` contient l'id de l'utilisateur.
