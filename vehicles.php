<?php
// Admin : gestion de la flotte (ajout, disponibilité, suppression)
require_once __DIR__ . '/../includes/auth.php';
$user = require_login(['admin'], '../');
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'add') {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = $_POST['type'] ?? '';
        $cap  = (int)($_POST['capacity_kg'] ?? 0);
        if (!preg_match('/^[A-Z0-9-]{2,20}$/', $code)) {
            flash('Code: 2 to 20 letters, digits or dashes (e.g. T-21).', 'bad');
        } elseif (!in_array($type, ['Truck', 'Van'], true)) {
            flash('Choose a vehicle type.', 'bad');
        } elseif ($cap < 100 || $cap > 20000) {
            flash('Capacity must be between 100 and 20000 kg.', 'bad');
        } else {
            try {
                $pdo->prepare("INSERT INTO vehicles (code, type, capacity_kg) VALUES (?, ?, ?)")->execute([$code, $type, $cap]);
                flash("Vehicle $code added.");
            } catch (PDOException $ex) {
                flash($ex->getCode() === '23000' ? "Vehicle $code already exists." : 'Database error.', 'bad');
            }
        }
    } elseif ($action === 'toggle') {
        $pdo->prepare("UPDATE vehicles SET status = IF(status = 'Available', 'Maintenance', 'Available') WHERE id = ?")->execute([$id]);
        flash('Vehicle status updated.');
    } elseif ($action === 'delete') {
        $st = $pdo->prepare("SELECT COUNT(*) FROM collections WHERE vehicle_id = ? AND status IN ('Scheduled','On the way')");
        $st->execute([$id]);
        if ($st->fetchColumn() > 0) {
            flash('This vehicle is used by active pickups. Reassign them first.', 'bad');
        } else {
            $pdo->prepare("DELETE FROM vehicles WHERE id = ?")->execute([$id]);
            flash('Vehicle deleted.', 'bad');
        }
    }
    redirect('vehicles.php');
}

$vehicles = $pdo->query(
    "SELECT v.*, SUM(c.status IN ('Scheduled','On the way')) AS active_count, COUNT(c.id) AS total_count
     FROM vehicles v LEFT JOIN collections c ON c.vehicle_id = v.id
     GROUP BY v.id ORDER BY v.code"
)->fetchAll();

$title = 'Vehicles'; $active = 'a-veh'; $root = '../';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><div><h1>Vehicles</h1><p class="muted">The fleet used for pickups.</p></div></div>

<div class="card tbl-wrap">
<table class="tbl">
  <thead><tr><th>Code</th><th>Type</th><th>Capacity</th><th>Status</th><th>Active pickups</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($vehicles as $v): ?>
    <tr>
      <td><b><?= e($v['code']) ?></b></td>
      <td><?= e($v['type']) ?></td>
      <td><?= number_format((int)$v['capacity_kg']) ?> kg</td>
      <td><span class="badge <?= $v['status'] === 'Available' ? 'ok' : 'warn' ?>"><?= e($v['status']) ?></span></td>
      <td><?= (int)$v['active_count'] ?></td>
      <td class="acts">
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
          <button class="btn sm ghost"><?= $v['status'] === 'Available' ? 'Set maintenance' : 'Set available' ?></button></form>
        <form method="post" onsubmit="return confirm('Delete <?= e($v['code']) ?>?');"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
          <button class="btn sm ghost danger">Delete</button></form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$vehicles): ?><tr><td colspan="6" class="muted center">No vehicles yet.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>

<form method="post" class="card form" novalidate>
  <h3>Add a vehicle</h3>
  <?= csrf_field() ?><input type="hidden" name="action" value="add">
  <div class="grid3">
    <div class="field"><label for="code">Code</label><input id="code" name="code" placeholder="T-21" maxlength="20"></div>
    <div class="field"><label for="type">Type</label><select id="type" name="type"><option>Truck</option><option>Van</option></select></div>
    <div class="field"><label for="cap">Capacity (kg)</label><input id="cap" name="capacity_kg" type="number" min="100" max="20000" value="1000"></div>
  </div>
  <div class="row end"><button class="btn primary">Add vehicle</button></div>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>
