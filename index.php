<?php
// Citoyen : My Collections (liste des pickups + annulation)
require_once __DIR__ . '/includes/auth.php';
$user = require_login(['citizen']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $st = db()->prepare("UPDATE collections SET status = 'Cancelled'
                         WHERE id = ? AND owner_name = ? AND status IN ('Scheduled','On the way')");
    $st->execute([$id, $user['name']]);
    $st->rowCount() ? flash('Pickup cancelled.', 'bad') : flash('This pickup cannot be cancelled.', 'bad');
    redirect('index.php');
}

$tab = ($_GET['tab'] ?? 'up') === 'hist' ? 'hist' : 'up';
$sql = COLLECTION_SELECT . " WHERE c.owner_name = ? " . COLLECTION_GROUP . " ORDER BY c.pickup_date, c.slot";
$st = db()->prepare($sql);
$st->execute([$user['name']]);
$all = $st->fetchAll();

$up   = array_values(array_filter($all, fn($c) => in_array($c['status'], ACTIVE_STATUSES, true)));
$hist = array_reverse(array_values(array_filter($all, fn($c) => !in_array($c['status'], ACTIVE_STATUSES, true))));
$list = $tab === 'up' ? $up : $hist;

$title = 'My Collections'; $active = 'mine'; $root = '';
include __DIR__ . '/includes/header.php';
?>
<div class="page-head">
  <div><h1>My Collections</h1><p class="muted">Pickups at your address, with the collector, the vehicle and the live status.</p></div>
  <a class="btn primary" href="request.php">+ Request pickup</a>
</div>

<div class="tabs">
  <a class="<?= $tab === 'up' ? 'on' : '' ?>" href="index.php?tab=up">Upcoming (<?= count($up) ?>)</a>
  <a class="<?= $tab === 'hist' ? 'on' : '' ?>" href="index.php?tab=hist">History (<?= count($hist) ?>)</a>
</div>

<?php if (!$list): ?>
  <div class="card empty"><b><?= $tab === 'up' ? 'No upcoming pickups' : 'Nothing here yet' ?></b>
    <span class="muted"><?= $tab === 'up' ? 'Request a pickup to see it here.' : 'Completed pickups appear here.' ?></span></div>
<?php endif; ?>

<?php foreach ($list as $c): ?>
  <article class="card">
    <div class="row between">
      <div><b><?= e(fmt_date($c['pickup_date'])) ?></b> <span class="muted">· <?= e($c['slot']) ?></span></div>
      <?= status_badge($c['status']) ?>
    </div>
    <div class="chips"><?= cats_badges($c['cats']) ?></div>
    <p class="muted sm"><?= e($c['address']) ?></p>
    <div class="who">
      <div><span class="lbl">Collector</span><b><?= e($c['collector_name'] ?? 'Unassigned') ?></b></div>
      <div><span class="lbl">Vehicle</span><b><?= e(vehicle_label($c['vehicle_code'], $c['vehicle_type'])) ?></b></div>
      <div><span class="lbl">Reference</span><b><?= e($c['code']) ?></b></div>
    </div>
    <?php if (in_array($c['status'], ACTIVE_STATUSES, true)): ?>
      <form method="post" onsubmit="return confirm('Cancel this pickup?');">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="cancel">
        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
        <button class="btn ghost sm">Cancel pickup</button>
      </form>
    <?php endif; ?>
  </article>
<?php endforeach; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
