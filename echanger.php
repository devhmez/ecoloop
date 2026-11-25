<?php
require_once __DIR__ . '/../includes/functions.php';
$user = require_login();
$pdo = db();

/* ---- Traitement de l'échange (POST) ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        $code = effectuer_echange((int)$user['id'], (int)($_POST['id'] ?? 0));
        flash("Échange réussi ! Ton code : $code. Présente-le au point partenaire pour retirer ta récompense.");
        redirect('frontoffice/mes_echanges.php');
    } catch (RuntimeException $ex) {
        flash($ex->getMessage(), 'bad');
        redirect('frontoffice/recompenses.php');
    }
}

/* ---- Page de confirmation (GET) ---- */
$s = $pdo->prepare('SELECT * FROM recompenses WHERE id = ? AND actif = 1');
$s->execute([(int)($_GET['id'] ?? 0)]);
$r = $s->fetch();
if (!$r) { flash('Récompense introuvable.', 'bad'); redirect('frontoffice/recompenses.php'); }
if ((int)$r['stock'] < 1)                     { flash('Cette récompense est en rupture de stock.', 'bad'); redirect('frontoffice/recompenses.php'); }
if ($user['points'] < $r['cout_points'])      { flash('Points insuffisants pour cette récompense.', 'bad'); redirect('frontoffice/recompenses.php'); }

$pageTitle = 'Confirmer l\'échange'; $area = 'app'; $active = 'rec';
require __DIR__ . '/../includes/header.php';
?>
<div class="ph"><div><h1>Confirmer l'échange</h1><p class="muted">Vérifie les détails avant de valider.</p></div></div>
<div class="card form-card stack">
  <div class="confirm"><?= reward_art($r, 'sm') ?><div><b><?= e($r['nom']) ?></b><span class="muted sm"><?= e($r['description']) ?></span></div></div>
  <ul class="kv">
    <li><span>Coût</span><b><?= fmt($r['cout_points']) ?> pts</b></li>
    <li><span>Solde actuel</span><b><?= fmt($user['points']) ?> pts</b></li>
    <li><span>Solde après l'échange</span><b><?= fmt($user['points'] - $r['cout_points']) ?> pts</b></li>
  </ul>
  <p class="muted sm">Tu recevras un code à présenter au point partenaire. Tant que l'échange est « en attente », tu peux l'annuler et récupérer tes points.</p>
  <form method="post" class="row" style="justify-content:flex-end">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
    <a class="btn btn-ghost" href="<?= url('frontoffice/recompenses.php') ?>">Annuler</a>
    <button class="btn btn-primary" type="submit">Confirmer l'échange</button>
  </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
