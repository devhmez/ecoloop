<?php
// Admin : toutes les collectes, assignation collecteur + véhicule, changement de statut
require_once __DIR__ . '/../includes/auth.php';
$user = require_login(['admin'], '../');
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $st = $pdo->prepare("SELECT * FROM collections WHERE id = ?");
    $st->execute([$id]);
    $col = $st->fetch();
    $back = 'collections.php' . (isset($_GET['status']) ? '?status=' . urlencode($_GET['status']) : '');

    if (!$col) {
        flash('Collection not found.', 'bad');
    } elseif (!in_array($col['status'], ACTIVE_STATUSES, true)) {
        flash("{$col['code']} is closed and cannot be changed.", 'bad');
    } elseif ($action === 'assign') {
        $collectorId = (int)($_POST['collector_id'] ?? 0) ?: null;
        $vehicleId   = (int)($_POST['vehicle_id'] ?? 0) ?: null;
        if (!$collectorId) $vehicleId = null;           // pas de collecteur => pas de véhicule
        $error = null;
        if ($collectorId) {
            $c = $pdo->prepare("SELECT COUNT(*) FROM collectors WHERE id = ? AND active = 1");
            $c->execute([$collectorId]);
            if (!$c->fetchColumn()) $error = 'Unknown collector.';
        }
        if (!$error && $vehicleId) {
            $v = $pdo->prepare("SELECT code, status FROM vehicles WHERE id = ?");
            $v->execute([$vehicleId]);
            $veh = $v->fetch();
            if (!$veh) $error = 'Unknown vehicle.';
            elseif ($veh['status'] !== 'Available' && (int)$col['vehicle_id'] !== $vehicleId) $error = "Vehicle {$veh['code']} is in maintenance.";
            elseif (vehicle_busy($vehicleId, $col['pickup_date'], $col['slot'], $id)) $error = "Vehicle {$veh['code']} is already booked for this date and slot.";
        }
        if ($error) {
            flash($error, 'bad');
        } else {
            $pdo->prepare("UPDATE collections SET collector_id = ?, vehicle_id = ? WHERE id = ?")->execute([$collectorId, $vehicleId, $id]);
            flash("{$col['code']} updated.");
        }
    } elseif ($action === 'advance') {
        $next = NEXT_STATUS[$col['status']] ?? null;
        if (!$next) {
            flash('No next status.', 'bad');
        } elseif ($next === 'On the way' && (!$col['collector_id'] || !$col['vehicle_id'])) {
            flash("Assign a collector and a vehicle to {$col['code']} first.", 'bad');
        } else {
            $pdo->prepare("UPDATE collections SET status = ? WHERE id = ?")->execute([$next, $id]);
            flash("{$col['code']} is now " . strtolower($next) . '.');
        }
    } elseif ($action === 'cancel') {
        $pdo->prepare("UPDATE collections SET status = 'Cancelled' WHERE id = ?")->execute([$id]);
        flash("{$col['code']} cancelled.", 'bad');
    }
    redirect($back);
}

$filter = in_array($_GET['status'] ?? '', STATUSES, true) ? $_GET['status'] : 'All';
$sql = COLLECTION_SELECT . ($filter !== 'All' ? " WHERE c.status = :s " : " ") . COLLECTION_GROUP . " ORDER BY c.pickup_date DESC, c.slot";
$st = $pdo->prepare($sql);
$filter !== 'All' ? $st->execute([':s' => $filter]) : $st->execute();
$rows = $st->fetchAll();

$collectors = $pdo->query("SELECT id, name FROM collectors WHERE active = 1 ORDER BY name")->fetchAll();
$vehicles   = $pdo->query("SELECT id, code, type, status FROM vehicles ORDER BY code")->fetchAll();

$title = 'Collections'; $active = 'a-col'; $root = '../';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><div><h1>Collections</h1><p class="muted">Assign collectors and vehicles, and track every pickup.</p></div></div>

<div class="tabs">
  <?php foreach (array_merge(['All'], STATUSES) as $s): ?>
    <a class="<?= $filter === $s ? 'on' : '' ?>" href="collections.php?status=<?= e($s) ?>"><?= e($s) ?></a>
  <?php endforeach; ?>
</div>

<div class="card tbl-wrap">
<table class="tbl">
  <thead><tr><th>ID</th><th>Citizen</th><th>When</th><th>Materials</th><th>Collector &amp; vehicle</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $c): $open = in_array($c['status'], ACTIVE_STATUSES, true); ?>
    <tr>
      <td><b><?= e($c['code']) ?></b></td>
      <td><?= e($c['owner_name']) ?><div class="muted xs"><?= e($c['address']) ?></div></td>
      <td><?= e(fmt_date($c['pickup_date'])) ?><div class="muted xs"><?= e($c['slot']) ?></div></td>
      <td><?= cats_badges($c['cats']) ?></td>
      <td>
        <?php if ($open): ?>
        <form method="post" class="inline-assign">
          <?= csrf_field() ?><input type="hidden" name="action" value="assign"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
          <select name="collector_id" aria-label="Collector for <?= e($c['code']) ?>">
            <option value="0">Unassigned</option>
            <?php foreach ($collectors as $co): ?><option value="<?= (int)$co['id'] ?>" <?= (int)$c['collector_id'] === (int)$co['id'] ? 'selected' : '' ?>><?= e($co['name']) ?></option><?php endforeach; ?>
          </select>
          <select name="vehicle_id" aria-label="Vehicle for <?= e($c['code']) ?>">
            <option value="0">No vehicle</option>
            <?php foreach ($vehicles as $v): $dis = $v['status'] !== 'Available' && (int)$c['vehicle_id'] !== (int)$v['id']; ?>
              <option value="<?= (int)$v['id'] ?>" <?= (int)$c['vehicle_id'] === (int)$v['id'] ? 'selected' : '' ?> <?= $dis ? 'disabled' : '' ?>><?= e($v['type'] . ' ' . $v['code']) ?><?= $dis ? ' (maintenance)' : '' ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn sm ghost">Save</button>
        </form>
        <?php else: ?>
          <?= e($c['collector_name'] ?? 'Unassigned') ?> · <?= e(vehicle_label($c['vehicle_code'], $c['vehicle_type'])) ?>
        <?php endif; ?>
      </td>
      <td><?= status_badge($c['status']) ?></td>
      <td class="acts">
        <?php if (isset(NEXT_STATUS[$c['status']])): ?>
          <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="advance"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
            <button class="btn sm ghost">→ <?= e(NEXT_STATUS[$c['status']]) ?></button></form>
        <?php endif; ?>
        <?php if ($open): ?>
          <form method="post" onsubmit="return confirm('Cancel <?= e($c['code']) ?>?');"><?= csrf_field() ?><input type="hidden" name="action" value="cancel"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
            <button class="btn sm ghost danger">Cancel</button></form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="7" class="muted center">Nothing in this state.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
