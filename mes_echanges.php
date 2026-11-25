<?php
require_once __DIR__ . '/../includes/functions.php';
$user = require_login();
$pdo = db();

$s = $pdo->prepare('SELECT e.*, r.nom AS recompense, r.icone, r.teinte
                    FROM echanges e JOIN recompenses r ON r.id = e.recompense_id
                    WHERE e.utilisateur_id = ? ORDER BY e.date_echange DESC');
$s->execute([$user['id']]);
$echanges = $s->fetchAll();

$s = $pdo->prepare('SELECT * FROM historique_points WHERE utilisateur_id = ? ORDER BY date_operation DESC, id DESC LIMIT 20');
$s->execute([$user['id']]);
$historique = $s->fetchAll();

$pageTitle = 'Mes échanges'; $area = 'app'; $active = 'ech';
require __DIR__ . '/../includes/header.php';
?>
<div class="ph"><div><h1>Mes échanges</h1><p class="muted">Suis tes récompenses et l'historique de tes points.</p></div>
  <a class="btn btn-primary" href="<?= url('frontoffice/recompenses.php') ?>"><?= icon('gift', 18) ?> Voir le catalogue</a></div>

<div class="card pad">
  <div class="card-h"><h3>Mes récompenses</h3><span class="muted sm"><?= count($echanges) ?> échange(s)</span></div>
  <?php if (!$echanges): ?>
    <div class="empty">Tu n'as pas encore échangé de points. <a href="<?= url('frontoffice/recompenses.php') ?>">Découvre le catalogue</a>.</div>
  <?php else: ?>
  <div class="tbl-wrap"><table class="tbl">
    <thead><tr><th>Récompense</th><th>Code</th><th>Points</th><th>Date</th><th>Statut</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($echanges as $x): ?>
      <tr>
        <td><div class="who"><?= reward_art(['teinte' => $x['teinte'], 'icone' => $x['icone']], 'xs') ?><b><?= e($x['recompense']) ?></b></div></td>
        <td><span class="code"><?= e($x['code']) ?></span> <button class="btn btn-sm btn-ghost" type="button" data-copy="<?= e($x['code']) ?>">Copier</button></td>
        <td><?= fmt($x['points_depenses']) ?> pts</td>
        <td class="muted"><?= fmt_date($x['date_echange']) ?></td>
        <td><?= badge_statut($x['statut']) ?></td>
        <td><?php if ($x['statut'] === 'en_attente'): ?>
          <form method="post" action="<?= url('frontoffice/annuler_echange.php') ?>" data-confirm="Annuler cet échange et récupérer tes points ?">
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$x['id'] ?>"><button class="btn btn-sm btn-danger" type="submit">Annuler</button></form>
        <?php endif; ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div>
  <?php endif; ?>
</div>

<div class="card pad gap-top">
  <div class="card-h"><h3>Historique des points</h3><span class="muted sm">20 dernières opérations</span></div>
  <?php if (!$historique): ?><div class="empty">Aucune opération pour le moment.</div><?php else: ?>
  <div class="tbl-wrap"><table class="tbl">
    <thead><tr><th>Opération</th><th>Date</th><th style="text-align:right">Points</th></tr></thead>
    <tbody><?php foreach ($historique as $h): ?>
      <tr><td><?= e($h['description']) ?></td><td class="muted"><?= fmt_date($h['date_operation']) ?></td>
        <td style="text-align:right"><span class="<?= $h['points'] >= 0 ? 'pos' : 'neg' ?>"><?= ($h['points'] >= 0 ? '+' : '−') . fmt(abs($h['points'])) ?></span></td></tr>
    <?php endforeach; ?></tbody></table></div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
