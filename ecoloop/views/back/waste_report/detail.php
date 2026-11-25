<?php
// View: one report (Back Office). Variables: $report, $csrf
require __DIR__ . '/../layout/header.php';
?>
<div class="a-head"><div><h1>Report <?= e($report['id']) ?></h1>
  <p class="muted"><a class="link" href="<?= url('admin.reports') ?>">← Back to all reports</a></p></div></div>

<div class="acard stack">
  <div class="row between"><?= cat_badge($report['cat']) ?><?= status_badge($report['status']) ?></div>
  <ul class="kv">
    <li><span>Location</span><b><?= e($report['loc']) ?></b></li>
    <li><span>Reported by</span><b><?= e($report['by']) ?></b></li>
    <li><span>Date</span><b><?= e($report['date']) ?></b></li>
    <li><span>Preference</span><b><?= e($report['pref']) ?></b></li>
  </ul>
  <div><p class="label">Description</p><p><?= e($report['desc']) ?></p></div>
  <div>
    <p class="label">Change status</p>
    <div class="stat-btns">
      <?php foreach (WasteReport::STATUSES as $s): ?>
        <form class="inline" method="post" action="<?= url('admin.status') ?>">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="id" value="<?= e($report['id']) ?>">
          <input type="hidden" name="status" value="<?= e($s) ?>">
          <button class="btn btn-sm <?= $report['status'] === $s ? 'btn-primary' : 'btn-ghost' ?>" type="submit" aria-pressed="<?= $report['status'] === $s ? 'true' : 'false' ?>"><?= e($s) ?></button>
        </form>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../layout/footer.php'; ?>
