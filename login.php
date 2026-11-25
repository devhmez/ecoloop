<?php
// Connexion minimale pour tester le module. Ton collègue en charge de l'authentification
// pourra la remplacer : il suffit que $_SESSION['uid'] contienne l'id de l'utilisateur connecté.
require_once __DIR__ . '/includes/functions.php';
if (current_user()) redirect('index.php');

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $s = db()->prepare('SELECT id, mot_de_passe, statut FROM utilisateurs WHERE email = ?');
    $s->execute([$email]);
    $row = $s->fetch();
    if ($row && password_verify($_POST['mot_de_passe'] ?? '', $row['mot_de_passe'])) {
        if ($row['statut'] !== 'actif') {
            $erreur = 'Ce compte est suspendu.';
        } else {
            session_regenerate_id(true);
            $_SESSION['uid'] = (int)$row['id'];
            redirect('index.php');
        }
    } else {
        $erreur = 'Email ou mot de passe incorrect.';
    }
}
?>
<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Connexion · EcoLoop</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap">
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>"></head>
<body><div class="login-wrap"><form class="card login stack" method="post" novalidate>
  <h1 style="font-size:26px">Connexion</h1>
  <p class="muted sm">Accède à tes EcoPoints et à tes récompenses.</p>
  <?php foreach (get_flashes() as $f): ?><div class="flash <?= e($f['t']) ?>"><?= e($f['m']) ?></div><?php endforeach; ?>
  <?php if ($erreur): ?><div class="flash bad"><?= e($erreur) ?></div><?php endif; ?>
  <?= csrf_field() ?>
  <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" autocomplete="username" required value="<?= e($_POST['email'] ?? '') ?>"></div>
  <div class="field"><label for="mdp">Mot de passe</label><input id="mdp" name="mot_de_passe" type="password" autocomplete="current-password" required></div>
  <button class="btn btn-primary btn-block" type="submit">Se connecter</button>
  <div class="demo-box"><b>Comptes de démo</b> (mot de passe : <span class="code">password</span>)<br>Citoyen : amira@ecoloop.tn<br>Admin : admin@ecoloop.tn</div>
</form></div></body></html>
