<?php
// Citoyen : demande de pickup
require_once __DIR__ . '/includes/auth.php';
$user = require_login(['citizen']);

$errors = [];
$old = ['cats' => [], 'date' => date('Y-m-d', strtotime('+1 day')), 'slot' => SLOTS[0],
        'address' => DEFAULT_ADDRESS, 'note' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $old['cats']    = array_values(array_intersect(array_keys(CATEGORIES), (array)($_POST['cats'] ?? [])));
    $old['date']    = trim($_POST['date'] ?? '');
    $old['slot']    = $_POST['slot'] ?? '';
    $old['address'] = trim($_POST['address'] ?? '');
    $old['note']    = trim($_POST['note'] ?? '');

    if (!$old['cats']) $errors['cats'] = 'Choose at least one waste category.';
    $d = DateTime::createFromFormat('Y-m-d', $old['date']);
    if (!$d || $d->format('Y-m-d') !== $old['date']) $errors['date'] = 'Choose a valid date.';
    elseif ($old['date'] <= date('Y-m-d')) $errors['date'] = 'Pickups are booked from tomorrow.';
    if (!in_array($old['slot'], SLOTS, true)) $errors['slot'] = 'Choose a time slot.';
    if (mb_strlen($old['address']) < 5 || mb_strlen($old['address']) > 255) $errors['address'] = 'Enter the pickup address.';
    if (mb_strlen($old['note']) > 500) $errors['note'] = 'Note is too long (500 characters max).';

    if (!$errors) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $st = $pdo->prepare("INSERT INTO collections (owner_name, pickup_date, slot, address, note)
                                 VALUES (?, ?, ?, ?, ?)");
            $st->execute([$user['name'], $old['date'], $old['slot'], $old['address'], $old['note'] ?: null]);
            $id = (int)$pdo->lastInsertId();
            $code = 'C-' . (1000 + $id);
            $pdo->prepare("UPDATE collections SET code = ? WHERE id = ?")->execute([$code, $id]);
            $ins = $pdo->prepare("INSERT INTO collection_categories (collection_id, category) VALUES (?, ?)");
            foreach ($old['cats'] as $k) $ins->execute([$id, $k]);
            $pdo->commit();
        } catch (Throwable $ex) {
            $pdo->rollBack();
            throw $ex;
        }
        flash("Pickup $code requested for " . date('D j M', strtotime($old['date'])) . '.');
        redirect('index.php');
    }
}

$title = 'Request a pickup'; $active = 'request'; $root = '';
include __DIR__ . '/includes/header.php';
?>
<div class="page-head"><div><h1>Request a pickup</h1>
  <p class="muted">A collector and a vehicle are assigned the evening before.</p></div></div>

<form method="post" class="card form" novalidate>
  <?= csrf_field() ?>
  <div class="field">
    <label>What are you putting out?</label>
    <div class="chips">
      <?php foreach (CATEGORIES as $k => $label): ?>
        <label class="chk"><input type="checkbox" name="cats[]" value="<?= e($k) ?>" <?= in_array($k, $old['cats'], true) ? 'checked' : '' ?>> <?= e($label) ?></label>
      <?php endforeach; ?>
    </div>
    <?php if (isset($errors['cats'])): ?><p class="err"><?= e($errors['cats']) ?></p><?php endif; ?>
  </div>
  <div class="grid2">
    <div class="field"><label for="date">Date</label>
      <input type="date" id="date" name="date" min="<?= e(date('Y-m-d', strtotime('+1 day'))) ?>" value="<?= e($old['date']) ?>">
      <?php if (isset($errors['date'])): ?><p class="err"><?= e($errors['date']) ?></p><?php endif; ?></div>
    <div class="field"><label for="slot">Time slot</label>
      <select id="slot" name="slot"><?php foreach (SLOTS as $s): ?><option <?= $old['slot'] === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?></select>
      <?php if (isset($errors['slot'])): ?><p class="err"><?= e($errors['slot']) ?></p><?php endif; ?></div>
  </div>
  <div class="field"><label for="address">Pickup address</label>
    <input id="address" name="address" value="<?= e($old['address']) ?>">
    <?php if (isset($errors['address'])): ?><p class="err"><?= e($errors['address']) ?></p><?php endif; ?></div>
  <div class="field"><label for="note">Note for the collector <span class="muted">(optional)</span></label>
    <textarea id="note" name="note" rows="2" placeholder="Bags are by the gate"><?= e($old['note']) ?></textarea>
    <?php if (isset($errors['note'])): ?><p class="err"><?= e($errors['note']) ?></p><?php endif; ?></div>
  <div class="row end"><a class="btn ghost" href="index.php">Cancel</a><button class="btn primary">Request pickup</button></div>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
