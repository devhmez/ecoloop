<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

/* ---- Changement de statut (POST) ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $nouveau = $_POST['statut'] ?? '';
    // Transitions autorisées
    $transitions = ['en_attente' => ['valide', 'annule'], 'valide' => ['remis', 'annule']];
    try {
        $s = $pdo->prepare('SELECT statut FROM echanges WHERE id = ?'); $s->execute([$id]);
        $actuel = $s->fetchColumn();
        if ($actuel === false || !in_array($nouveau, $transitions[$actuel] ?? [], true)) throw new RuntimeException('Changement de statut non autorisé.');
        if ($nouveau === 'annule') {
            annuler_echange($id);   // rembourse les points + remet en stock
            flash('Échange annulé, points remboursés.');
        } else {
            $pdo->prepare('UPDATE echanges SET statut = ? WHERE id = ?')->execute([$nouveau, $id]);
            flash('Statut mis à jour.');
        }
    } catch (RuntimeException $ex) { flash($ex->getMessage(), 'bad'); }
    redirect('backoffice/echanges.php' . (!empty($_POST['f']) ? '?statut=' . urlencode($_POST['f']) : ''));
}

$filtre = $_GET['statut'] ?? '';
$sql = 'SELECT e.*, u.nom AS utilisateur, u.email, r.nom AS recompense
        FROM echanges e JOIN utilisateurs u ON u.id = e.utilisateur_id JOIN recompenses r ON r.id = e.recompense_id';
$p = [];
if (isset(STATUTS[$filtre])) { $sql .= ' WHERE e.statut = ?'; $p[] = $filtre; }
$sql .= ' ORDER BY e.date_echange DESC';
$s = $pdo->prepare($sql); $s->execute($p);
$rows = $s->fetchAll();

$pageTitle = 'Échanges'; $area = 'admin'; $active = 'ech';
require __DIR__ . '/../includes/header.php';
?>
<div class="ph"><div><h1>Échanges</h1><p class="muted">Valide et remets les récompenses demandées par les citoyens.</p></div>
  <div class="seg">
    <a class="<?= $filtre === '' ? 'on' : '' ?>" href="<?= url('backoffice/echanges.php') ?>">Tous</a>
    <?php foreach (STATUTS as $k => [$l]): ?><a class="<?= $filtre === $k ? 'on' : '' ?>" href="<?= url('backoffice/echanges.php?statut=' . $k) ?>"><?= e($l) ?></a><?php endforeach; ?>
  </div></div>
<div class="card pad">
  <?php if (!$rows): ?><div class="empty">Aucun échange.</div><?php else: ?>
  <div class="tbl-wrap"><table class="tbl">
    <thead><tr><th>Code</th><th>Citoyen</th><th>Récompense</th><th>Points</th><th>Date</th><th>Statut</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $x): ?>
      <tr>
        <td><span class="code"><?= e($x['code']) ?></span></td>
        <td><div class="who"><span class="av sm"><?= e(initials($x['utilisateur'])) ?></span><div><b><?= e($x['utilisateur']) ?></b><span class="muted xs"><?= e($x['email']) ?></span></div></div></td>
        <td><?= e($x['recompense']) ?></td><td><?= fmt($x['points_depenses']) ?></td><td class="muted"><?= fmt_date($x['date_echange']) ?></td>
        <td><?= badge_statut($x['statut']) ?></td>
        <td><div class="acts">
          <?php
          $btns = $x['statut'] === 'en_attente' ? [['valide', 'Valider', 'btn-primary']] : ($x['statut'] === 'valide' ? [['remis', 'Marquer remis', 'btn-primary']] : []);
          if (in_array($x['statut'], ['en_attente', 'valide'], true)) $btns[] = ['annule', 'Annuler', 'btn-danger'];
          foreach ($btns as [$st, $lab, $cls]): ?>
            <form method="post" <?= $st === 'annule' ? 'data-confirm="Annuler cet échange et rembourser les points ?"' : '' ?>>
              <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$x['id'] ?>"><input type="hidden" name="statut" value="<?= $st ?>"><input type="hidden" name="f" value="<?= e($filtre) ?>">
              <button class="btn btn-sm <?= $cls ?>" type="submit"><?= $lab ?></button></form>
          <?php endforeach; ?>
        </div></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
