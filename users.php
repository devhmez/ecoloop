<?php
// Admin : créer et gérer les comptes (surtout les chauffeurs)
require_once __DIR__ . '/../includes/auth.php';
$me  = require_login(['admin'], '../');
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'create_driver') {
        $name  = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $pass  = (string)($_POST['password'] ?? '');
        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            flash('Enter the driver name (2 to 100 characters).', 'bad');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            flash('Enter a valid email address.', 'bad');
        } elseif (mb_strlen($pass) < 8) {
            flash('Password must have at least 8 characters.', 'bad');
        } else {
            $pdo->beginTransaction();
            try {
                // 1) le chauffeur existe comme "collecteur" (assignable aux collectes)
                $pdo->prepare("INSERT INTO collectors (name) VALUES (?)")->execute([$name]);
                $collectorId = (int)$pdo->lastInsertId();
                // 2) son compte de connexion, lié au collecteur
                $pdo->prepare("INSERT INTO users (name, email, password_hash, role, collector_id) VALUES (?, ?, ?, 'driver', ?)")
                    ->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT), $collectorId]);
                $pdo->commit();
                flash("Driver account created for $name ($email).");
            } catch (PDOException $ex) {
                $pdo->rollBack();
                flash($ex->getCode() === '23000' ? 'This email or driver name already exists.' : 'Database error.', 'bad');
            }
        }
    } elseif ($action === 'toggle') {
        $st = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $st->execute([$id]);
        $target = $st->fetch();
        if (!$target) {
            flash('User not found.', 'bad');
        } elseif ((int)$target['id'] === (int)$me['id']) {
            flash('You cannot deactivate your own account.', 'bad');
        } else {
            $newActive = $target['active'] ? 0 : 1;
            $busy = 0;
            if ($target['role'] === 'driver' && !$newActive) {
                $c = $pdo->prepare("SELECT COUNT(*) FROM collections WHERE collector_id = ? AND status IN ('Scheduled','On the way')");
                $c->execute([$target['collector_id']]);
                $busy = (int)$c->fetchColumn();
            }
            if ($busy) {
                flash("{$target['name']} still has $busy active pickup(s). Reassign them first.", 'bad');
            } else {
                $pdo->prepare("UPDATE users SET active = ? WHERE id = ?")->execute([$newActive, $id]);
                if ($target['role'] === 'driver' && $target['collector_id']) {
                    $pdo->prepare("UPDATE collectors SET active = ? WHERE id = ?")->execute([$newActive, $target['collector_id']]);
                }
                flash($newActive ? "{$target['name']} activated." : "{$target['name']} deactivated.", $newActive ? 'ok' : 'bad');
            }
        }
    } elseif ($action === 'reset_password') {
        $pass = (string)($_POST['password'] ?? '');
        if (mb_strlen($pass) < 8) {
            flash('New password must have at least 8 characters.', 'bad');
        } else {
            $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([password_hash($pass, PASSWORD_DEFAULT), $id]);
            flash('Password updated.');
        }
    }
    redirect('users.php');
}

$users = $pdo->query(
    "SELECT id, name, email, role, active FROM users ORDER BY FIELD(role,'admin','driver','citizen'), name"
)->fetchAll();

$title = 'Users'; $active = 'a-usr'; $root = '../';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><div><h1>Users</h1><p class="muted">Create driver accounts and manage access.</p></div></div>

<form method="post" class="card form" novalidate>
  <h3>Create a driver account</h3>
  <?= csrf_field() ?><input type="hidden" name="action" value="create_driver">
  <div class="grid3">
    <div class="field"><label for="name">Full name</label><input id="name" name="name" placeholder="Hichem Mansouri" maxlength="100"></div>
    <div class="field"><label for="email">Email (login)</label><input id="email" name="email" type="email" placeholder="driver@ecoloop.test" maxlength="150"></div>
    <div class="field"><label for="password">Password (8+ characters)</label><input id="password" name="password" type="password" autocomplete="new-password"></div>
  </div>
  <p class="muted sm">The driver is also added to the list of collectors, so you can assign pickups and a vehicle to them.</p>
  <div class="row end"><button class="btn primary">Create driver</button></div>
</form>

<div class="card tbl-wrap">
<table class="tbl">
  <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Reset password</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($users as $x): ?>
    <tr>
      <td><b><?= e($x['name']) ?></b></td>
      <td><?= e($x['email']) ?></td>
      <td><span class="badge <?= $x['role'] === 'driver' ? 'info' : ($x['role'] === 'admin' ? 'warn' : 'mute') ?>"><?= e($x['role']) ?></span></td>
      <td><span class="badge <?= $x['active'] ? 'ok' : 'bad' ?>"><?= $x['active'] ? 'Active' : 'Disabled' ?></span></td>
      <td>
        <form method="post" class="inline-assign"><?= csrf_field() ?><input type="hidden" name="action" value="reset_password"><input type="hidden" name="id" value="<?= (int)$x['id'] ?>">
          <input type="password" name="password" placeholder="New password" autocomplete="new-password" aria-label="New password for <?= e($x['name']) ?>" style="width:150px">
          <button class="btn sm ghost">Set</button></form>
      </td>
      <td class="acts">
        <?php if ((int)$x['id'] !== (int)$me['id']): ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$x['id'] ?>">
          <button class="btn sm ghost <?= $x['active'] ? 'danger' : '' ?>"><?= $x['active'] ? 'Deactivate' : 'Activate' ?></button></form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
